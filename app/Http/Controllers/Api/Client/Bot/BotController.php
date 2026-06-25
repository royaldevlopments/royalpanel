<?php

namespace RoyalPanel\Http\Controllers\Api\Client\Bot;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RoyalPanel\Models\DiscordLink;
use RoyalPanel\Models\Discord2FACode;
use RoyalPanel\Models\User;
use RoyalPanel\Models\Server;
use RoyalPanel\Models\Node;
use RoyalPanel\Models\Nest;
use RoyalPanel\Models\Egg;
use RoyalPanel\Models\Location;
use RoyalPanel\Models\DatabaseHost;
use RoyalPanel\Models\Allocation;
use RoyalPanel\Http\Controllers\Controller;
use RoyalPanel\Repositories\Wings\DaemonPowerRepository;
use RoyalPanel\Repositories\Wings\DaemonCommandRepository;
use RoyalPanel\Contracts\Repository\SettingsRepositoryInterface;

class BotController extends Controller
{
    public function __construct(
        private SettingsRepositoryInterface $settings,
        private DaemonPowerRepository $powerRepository,
        private DaemonCommandRepository $commandRepository,
    ) {}

    private function getBotToken(): string
    {
        return $this->settings->get('settings::royal:botToken', '');
    }

    private function isBotAuthorized(Request $request): bool
    {
        $token = $request->bearerToken() ?? $request->header('X-Bot-Token');
        return $token && $token === $this->getBotToken();
    }

    private function getUserFromDiscord(string $discordId): ?User
    {
        $link = DiscordLink::where('discord_id', $discordId)->first();
        return $link?->user;
    }

    // ─── Link System ───────────────────────────────────────────────

    public function generateCode(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $code = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

        DiscordLink::updateOrCreate(
            ['user_id' => $user->id],
            [
                'code' => $code,
                'expires_at' => Carbon::now()->addMinutes(5),
            ]
        );

        return response()->json(['code' => $code, 'expires_in' => 300]);
    }

    public function verifyLink(Request $request): JsonResponse
    {
        if (!$this->isBotAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized bot'], 403);
        }

        $data = $request->validate([
            'code' => 'required|string|size:6',
            'discord_id' => 'required|string',
        ]);

        $link = DiscordLink::where('code', $data['code'])
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$link) {
            return response()->json(['error' => 'Invalid or expired code'], 422);
        }

        $existing = DiscordLink::where('discord_id', $data['discord_id'])->first();
        if ($existing && $existing->user_id !== $link->user_id) {
            $existing->update(['discord_id' => null, 'linked_at' => null]);
        }

        $link->update([
            'discord_id' => $data['discord_id'],
            'code' => null,
            'expires_at' => null,
            'linked_at' => Carbon::now(),
        ]);

        $user = $link->user;

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
            ],
        ]);
    }

    public function linkStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        $link = DiscordLink::where('user_id', $user->id)->first();

        return response()->json([
            'linked' => $link && $link->discord_id !== null,
            'discord_id' => $link?->discord_id,
            'linked_at' => $link?->linked_at,
        ]);
    }

    public function unlink(Request $request): JsonResponse
    {
        if ($this->isBotAuthorized($request)) {
            $data = $request->validate(['discord_id' => 'required|string']);
            $link = DiscordLink::where('discord_id', $data['discord_id'])->first();
        } else {
            $user = $request->user();
            if (!$user) return response()->json(['error' => 'Unauthorized'], 403);
            $link = DiscordLink::where('user_id', $user->id)->first();
        }

        if (!$link) return response()->json(['error' => 'No link found'], 404);

        $link->update(['discord_id' => null, 'linked_at' => null]);

        return response()->json(['success' => true]);
    }

    // ─── Autocomplete Endpoints ───────────────────────────────────

    public function listServers(Request $request): JsonResponse
    {
        if (!$this->isBotAuthorized($request) && !$request->user()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($this->isBotAuthorized($request)) {
            $discordId = $request->input('discord_id');
            if ($discordId) {
                $panelUser = $this->getUserFromDiscord($discordId);
                if (!$panelUser) return response()->json([]);
                $servers = Server::where('owner_id', $panelUser->id)->get(['id', 'uuid', 'uuidShort', 'name', 'node_id', 'owner_id', 'description', 'status']);
            } elseif ($request->input('all') === 'true') {
                $servers = Server::get(['id', 'uuid', 'uuidShort', 'name', 'node_id', 'owner_id', 'description', 'status']);
            } else {
                return response()->json(['error' => 'discord_id or all=true required'], 422);
            }
        } else {
            $servers = Server::where('owner_id', $request->user()->id)->get(['id', 'uuid', 'uuidShort', 'name', 'node_id', 'owner_id', 'description', 'status']);
        }

        return response()->json($servers);
    }

    public function listUsers(Request $request): JsonResponse
    {
        if (!$this->isBotAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = $request->input('q', '');
        $users = User::where('username', 'like', "%$query%")
            ->orWhere('email', 'like', "%$query%")
            ->limit(25)
            ->get(['id', 'username', 'email', 'root_admin']);

        return response()->json($users);
    }

    public function listNodes(Request $request): JsonResponse
    {
        if (!$this->isBotAuthorized($request) && !$request->user()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $nodes = Node::withCount('servers')->get(['id', 'name', 'fqdn', 'location_id', 'memory', 'disk']);
        return response()->json($nodes);
    }

    public function listNests(Request $request): JsonResponse
    {
        if (!$this->isBotAuthorized($request) && !$request->user()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $nests = Nest::withCount('eggs')->get(['id', 'name', 'description']);
        return response()->json($nests);
    }

    public function listEggs(Request $request): JsonResponse
    {
        if (!$this->isBotAuthorized($request) && !$request->user()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $nestId = $request->input('nest_id');
        $eggs = $nestId
            ? Egg::where('nest_id', $nestId)->get(['id', 'nest_id', 'name', 'description', 'docker_image'])
            : Egg::get(['id', 'nest_id', 'name', 'description', 'docker_image']);

        return response()->json($eggs);
    }

    public function listLocations(Request $request): JsonResponse
    {
        if (!$this->isBotAuthorized($request) && !$request->user()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json(Location::get(['id', 'short', 'long']));
    }

    public function listAllocations(Request $request): JsonResponse
    {
        if (!$this->isBotAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $nodeId = $request->input('node_id');
        if (!$nodeId) return response()->json(['error' => 'node_id required'], 422);

        $allocations = Allocation::where('node_id', $nodeId)
            ->whereNull('server_id')
            ->get(['id', 'ip', 'port', 'ip_alias']);

        return response()->json($allocations);
    }

    public function listDatabaseHosts(Request $request): JsonResponse
    {
        if (!$this->isBotAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json(DatabaseHost::get(['id', 'name', 'host', 'port']));
    }

    // ─── Server Actions ──────────────────────────────────────────

    public function serverInfo(Request $request, string $id): JsonResponse
    {
        if (!$this->isBotAuthorized($request) && !$request->user()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $server = Server::with(['node', 'egg'])->findOrFail($id);
        $primaryAlloc = $server->allocations()->where('primary', true)->first();

        return response()->json([
            'id' => $server->id,
            'uuid' => $server->uuid,
            'uuidShort' => $server->uuidShort,
            'name' => $server->name,
            'description' => $server->description,
            'status' => $server->status,
            'node' => $server->node ? ['id' => $server->node->id, 'name' => $server->node->name] : null,
            'egg' => $server->egg ? ['id' => $server->egg->id, 'name' => $server->egg->name] : null,
            'allocation' => $primaryAlloc ? ['ip' => $primaryAlloc->ip, 'port' => $primaryAlloc->port] : null,
            'limits' => [
                'memory' => $server->memory,
                'swap' => $server->swap,
                'disk' => $server->disk,
                'io' => $server->io,
                'cpu' => $server->cpu,
                'threads' => $server->threads,
            ],
            'owner_id' => $server->owner_id,
            'created_at' => $server->created_at,
        ]);
    }

    public function serverPower(Request $request, string $id): JsonResponse
    {
        if (!$this->isBotAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $action = $request->validate(['action' => 'required|in:start,stop,restart,kill'])['action'];
        $server = Server::findOrFail($id);

        try {
            $this->powerRepository->setServer($server)->send($action);
            return response()->json(['success' => true, 'action' => $action]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function serverCommand(Request $request, string $id): JsonResponse
    {
        if (!$this->isBotAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate(['command' => 'required|string']);
        $server = Server::findOrFail($id);

        try {
            $this->commandRepository->setServer($server)->send($data['command']);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─── Admin CRUD ──────────────────────────────────────────────

    public function createUser(Request $request): JsonResponse
    {
        if (!$this->isBotAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'admin' => 'boolean',
        ]);

        $user = User::create([
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => bcrypt($data['password']),
            'root_admin' => $data['admin'] ?? false,
        ]);

        return response()->json([
            'success' => true,
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
        ], 201);
    }

    public function updateUser(Request $request, string $id): JsonResponse
    {
        if (!$this->isBotAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);
        $data = $request->validate([
            'email' => 'email|unique:users,email,' . $id,
            'username' => 'string|max:255',
            'password' => 'string|min:8',
            'admin' => 'boolean',
        ]);

        if (isset($data['password'])) $data['password'] = bcrypt($data['password']);
        $user->update($data);

        return response()->json(['success' => true]);
    }

    public function deleteUser(Request $request, string $id): JsonResponse
    {
        if (!$this->isBotAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        User::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function userAction(Request $request, string $id): JsonResponse
    {
        if (!$this->isBotAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $action = $request->validate(['action' => 'required|in:suspend,unsuspend'])['action'];
        User::findOrFail($id);

        Server::where('owner_id', $id)->update([
            'status' => $action === 'suspend' ? 'suspended' : 'installed',
        ]);

        return response()->json(['success' => true, 'action' => $action]);
    }

    public function serverAction(Request $request, string $id): JsonResponse
    {
        if (!$this->isBotAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $action = $request->validate(['action' => 'required|in:delete,suspend,unsuspend,reinstall'])['action'];
        $server = Server::findOrFail($id);

        match ($action) {
            'delete' => $server->delete(),
            'suspend' => $server->update(['status' => 'suspended']),
            'unsuspend' => $server->update(['status' => 'installed']),
            'reinstall' => $server->update(['status' => 'installing', 'installed' => 0]),
        };

        return response()->json(['success' => true, 'action' => $action]);
    }

    public function updateServerLimits(Request $request, string $id): JsonResponse
    {
        if (!$this->isBotAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'memory' => 'integer|min:0',
            'swap' => 'integer|min:0',
            'disk' => 'integer|min:0',
            'io' => 'integer|min:10|max:1000',
            'cpu' => 'integer|min:0',
            'threads' => 'nullable|string',
            'database_limit' => 'integer|min:0',
            'allocation_limit' => 'integer|min:0',
            'backup_limit' => 'integer|min:0',
        ]);

        Server::findOrFail($id)->update($data);
        return response()->json(['success' => true]);
    }

    // ─── Stats ───────────────────────────────────────────────────

    public function stats(Request $request): JsonResponse
    {
        if (!$this->isBotAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'total_users' => User::count(),
            'total_servers' => Server::count(),
            'total_nodes' => Node::count(),
            'total_locations' => Location::count(),
            'total_eggs' => Egg::count(),
            'total_nests' => Nest::count(),
            'total_db_hosts' => DatabaseHost::count(),
            'suspended_servers' => Server::where('status', 'suspended')->count(),
            'installing_servers' => Server::where('status', 'installing')->count(),
            'active_servers' => Server::where('status', 'installed')->count(),
            'linked_discord_users' => DiscordLink::whereNotNull('discord_id')->count(),
        ]);
    }

    public function config(Request $request): JsonResponse
    {
        if (!$this->isBotAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'panel_url' => config('app.url'),
            'panel_name' => config('app.name'),
            'discord_bot_token' => $this->settings->get('settings::royal:discordBotToken', ''),
            'discord_guild_id' => $this->settings->get('settings::royal:discordGuildId', ''),
            'discord_admin_role_id' => $this->settings->get('settings::royal:discordAdminRoleId', ''),
        ]);
    }

    // ─── Discord 2FA ────────────────────────────────────────────

    public function getPending2FACodes(Request $request): JsonResponse
    {
        if (!$this->isBotAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $codes = Discord2FACode::where('sent', false)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->get(['id', 'discord_id', 'code']);

        return response()->json($codes);
    }

    public function mark2FACodeSent(Request $request): JsonResponse
    {
        if (!$this->isBotAuthorized($request)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate(['id' => 'required|integer']);
        Discord2FACode::where('id', $data['id'])->update(['sent' => true]);

        return response()->json(['success' => true]);
    }

    public function toggle2FA(Request $request): JsonResponse
    {
        if ($this->isBotAuthorized($request)) {
            $data = $request->validate([
                'discord_id' => 'required|string',
                'enabled' => 'required|boolean',
            ]);
            $link = DiscordLink::where('discord_id', $data['discord_id'])->first();
        } else {
            $user = $request->user();
            if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);
            $data = $request->validate(['enabled' => 'required|boolean']);
            $link = DiscordLink::where('user_id', $user->id)->first();
        }

        if (!$link || !$link->discord_id) {
            return response()->json(['error' => 'Discord account not linked'], 400);
        }

        $link->update(['discord_2fa_enabled' => $data['enabled']]);

        if ($data['enabled']) {
            User::where('id', $link->user_id)->update(['use_totp' => true]);
        }

        return response()->json(['success' => true, 'enabled' => $data['enabled']]);
    }

    public function get2FAStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        $link = DiscordLink::where('user_id', $user->id)->first();

        return response()->json([
            'linked' => $link && $link->discord_id !== null,
            'enabled' => $link ? $link->discord_2fa_enabled : false,
        ]);
    }
}
