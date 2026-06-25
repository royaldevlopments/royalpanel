import { Client, GatewayIntentBits, REST, Routes, Collection } from 'discord.js';
import axios from 'axios';

// ─── Bootstrap: fetch config from panel ────────────────────

const PANEL_URL = process.env.PANEL_URL || 'https://papa.codenestsolution.in';
const BOT_TOKEN = process.env.BOT_TOKEN;

if (!BOT_TOKEN) {
  console.error('❌ BOT_TOKEN environment variable is required.');
  console.log('Usage: PANEL_URL=https://panel.url BOT_TOKEN=xxx node index.js');
  process.exit(1);
}

const api = axios.create({
  baseURL: PANEL_URL.replace(/\/+$/, ''),
  headers: { 'X-Bot-Token': BOT_TOKEN, 'Accept': 'application/json' },
});

let config;
try {
  const { data } = await api.get('/api/client/bot/config');
  config = data;
  console.log(`✅ Fetched config from ${PANEL_URL}`);
} catch (e) {
  console.error('❌ Failed to fetch config from panel:', e.message);
  process.exit(1);
}

if (!config.discord_bot_token) {
  console.error('❌ discord_bot_token is empty. Set it in Admin → Royal Theme → Advanced → Discord Bot Token');
  console.log('⏳ Retrying in 30 seconds...');
  setTimeout(() => process.exit(0), 30000); // exit so systemd restarts
}

// ─── Client ────────────────────────────────────────────────

const client = new Client({
  intents: [GatewayIntentBits.Guilds, GatewayIntentBits.GuildMessages, GatewayIntentBits.DirectMessages],
});

// ─── Helpers ──────────────────────────────────────────────

function color(status) {
  const map = { installed: 0x22c55e, installing: 0xa855f7, suspended: 0xef4444, active: 0x22c55e };
  return map[status] || 0x3b82f6;
}

function statusEmoji(s) {
  const map = { installed: '🟢', installing: '🟣', suspended: '🔴', active: '🟢' };
  return map[s] || '⚪';
}

function buildEmbed(opts) {
  return {
    color: opts.color || 0x3b82f6,
    title: opts.title,
    description: opts.description,
    fields: opts.fields || [],
    footer: opts.footer ? { text: opts.footer } : undefined,
    timestamp: opts.timestamp ? new Date().toISOString() : undefined,
    thumbnail: opts.thumbnail ? { url: opts.thumbnail } : undefined,
  };
}

function isAdmin(member) {
  if (config.discord_admin_role_id) {
    return member.roles.cache.has(config.discord_admin_role_id) || member.permissions.has('Administrator');
  }
  return member.permissions.has('Administrator');
}

function requireAdmin(interaction) {
  if (!isAdmin(interaction.member)) {
    interaction.reply({ embeds: [{ color: 0xef4444, description: '❌ You need admin role to use this command.' }], ephemeral: true });
    return false;
  }
  return true;
}

async function get(endpoint) {
  const { data } = await api.get(endpoint);
  return data;
}

async function post(endpoint, body) {
  const { data } = await api.post(endpoint, body);
  return data;
}

async function patch(endpoint, body) {
  const { data } = await api.patch(endpoint, body);
  return data;
}

async function del(endpoint) {
  const { data } = await api.delete(endpoint);
  return data;
}

// ─── Command Registry ─────────────────────────────────────

const commands = new Collection();

function cmd(name, handler) {
  commands.set(name, handler);
}

// ─── LINK COMMANDS ─────────────────────────────────────────

cmd('link', async (interaction) => {
  const code = interaction.options.getString('code');
  try {
    const res = await post('/api/client/bot/link/verify', { code, discord_id: interaction.user.id });
    await interaction.reply({
      embeds: [buildEmbed({
        color: 0x22c55e, title: '✅ Account Linked',
        fields: [
          { name: 'Panel User', value: res.user.username, inline: true },
          { name: 'Email', value: res.user.email, inline: true },
        ],
        footer: 'You can now use all bot commands.',
        timestamp: true,
      })],
      ephemeral: true,
    });
  } catch (e) {
    await interaction.reply({
      embeds: [{ color: 0xef4444, description: `❌ ${e.response?.data?.error || 'Invalid or expired code'}` }],
      ephemeral: true,
    });
  }
});

cmd('unlink', async (interaction) => {
  try {
    await post('/api/client/bot/link/unlink', { discord_id: interaction.user.id });
    await interaction.reply({ embeds: [{ color: 0x22c55e, description: '✅ Account unlinked.' }], ephemeral: true });
  } catch {
    await interaction.reply({ embeds: [{ color: 0xef4444, description: '❌ No link found.' }], ephemeral: true });
  }
});

cmd('status', async (interaction) => {
  try {
    const servers = await get(`/api/client/bot/servers?discord_id=${interaction.user.id}`);
    await interaction.reply({
      embeds: [buildEmbed({
        color: 0x3b82f6, title: '👤 Your Account',
        fields: [
          { name: 'Discord', value: `<@${interaction.user.id}>`, inline: true },
          { name: 'Servers', value: String(servers.length), inline: true },
          { name: 'Linked', value: '✅ Yes', inline: true },
        ],
        footer: config.panel_name,
        timestamp: true,
      })],
      ephemeral: true,
    });
  } catch {
    await interaction.reply({ embeds: [{ color: 0xef4444, description: '❌ Not linked. Use /link to connect.' }], ephemeral: true });
  }
});

// ─── USER SERVER COMMANDS ──────────────────────────────────

async function resolveServer(interaction) {
  const serverId = interaction.options.getString('server');
  const servers = await get(`/api/client/bot/servers?discord_id=${interaction.user.id}`);
  return serverId ? servers.find(s => s.uuidShort === serverId || s.id == serverId || s.name === serverId) : null;
}

cmd('my-servers', async (interaction) => {
  try {
    const servers = await get(`/api/client/bot/servers?discord_id=${interaction.user.id}`);
    if (!servers.length) return interaction.reply({ embeds: [{ color: 0xef4444, description: '❌ No servers found.' }], ephemeral: true });
    await interaction.reply({
      embeds: [buildEmbed({
        color: 0x3b82f6, title: '🖥️ Your Servers',
        fields: servers.slice(0, 25).map(s => ({
          name: `${statusEmoji(s.status)} ${s.name}`,
          value: `\`${s.uuidShort}\` | ${s.status || 'unknown'}`,
          inline: true,
        })),
        footer: `${servers.length} total`,
        timestamp: true,
      })],
      ephemeral: true,
    });
  } catch {
    await interaction.reply({ embeds: [{ color: 0xef4444, description: '❌ Not linked. Use /link first.' }], ephemeral: true });
  }
});

cmd('server-info', async (interaction) => {
  try {
    const server = await resolveServer(interaction);
    if (!server) return interaction.reply({ embeds: [{ color: 0xef4444, description: '❌ Server not found.' }], ephemeral: true });
    const info = await get(`/api/client/bot/servers/${server.id}`);
    await interaction.reply({
      embeds: [buildEmbed({
        color: color(info.status), title: `${statusEmoji(info.status)} ${info.name}`,
        fields: [
          { name: 'Status', value: info.status || 'unknown', inline: true },
          { name: 'Node', value: info.node?.name || 'N/A', inline: true },
          { name: 'Egg', value: info.egg?.name || 'N/A', inline: true },
          { name: 'Address', value: info.allocation ? `${info.allocation.ip}:${info.allocation.port}` : 'N/A', inline: true },
          { name: 'CPU', value: `${info.limits.cpu || 0}%`, inline: true },
          { name: 'Memory', value: `${info.limits.memory} MB`, inline: true },
          { name: 'Disk', value: `${info.limits.disk} MB`, inline: true },
          { name: 'ID', value: `\`${info.uuidShort}\``, inline: true },
        ],
        footer: `Created ${new Date(info.created_at).toLocaleDateString()}`,
        timestamp: true,
      })],
    });
  } catch {
    await interaction.reply({ embeds: [{ color: 0xef4444, description: '❌ Server not found.' }], ephemeral: true });
  }
});

cmd('server-power', async (interaction) => {
  try {
    const server = await resolveServer(interaction);
    if (!server) return interaction.reply({ embeds: [{ color: 0xef4444, description: '❌ Server not found.' }], ephemeral: true });
    const action = interaction.options.getString('action');
    await post(`/api/client/bot/servers/${server.id}/power`, { action });
    await interaction.reply({ embeds: [{ color: 0x22c55e, description: `✅ ${action} sent to **${server.name}**` }] });
  } catch (e) {
    await interaction.reply({ embeds: [{ color: 0xef4444, description: `❌ ${e.response?.data?.error || 'Failed'}` }], ephemeral: true });
  }
});

cmd('server-command', async (interaction) => {
  try {
    const server = await resolveServer(interaction);
    if (!server) return interaction.reply({ embeds: [{ color: 0xef4444, description: '❌ Server not found.' }], ephemeral: true });
    const command = interaction.options.getString('command');
    await post(`/api/client/bot/servers/${server.id}/command`, { command });
    await interaction.reply({ embeds: [{ color: 0x22c55e, description: `✅ Command sent to **${server.name}**\n\`${command}\`` }] });
  } catch (e) {
    await interaction.reply({ embeds: [{ color: 0xef4444, description: `❌ ${e.response?.data?.error || 'Failed'}` }], ephemeral: true });
  }
});

// ─── ADMIN COMMANDS ────────────────────────────────────────

cmd('admin-stats', async (interaction) => {
  if (!requireAdmin(interaction)) return;
  const stats = await get('/api/client/bot/stats');
  await interaction.reply({
    embeds: [buildEmbed({
      color: 0xa855f7, title: '📊 Panel Statistics',
      fields: [
        { name: 'Users', value: String(stats.total_users), inline: true },
        { name: 'Servers', value: String(stats.total_servers), inline: true },
        { name: 'Nodes', value: String(stats.total_nodes), inline: true },
        { name: 'Locations', value: String(stats.total_locations), inline: true },
        { name: 'Eggs', value: String(stats.total_eggs), inline: true },
        { name: 'DB Hosts', value: String(stats.total_db_hosts), inline: true },
        { name: '🟢 Active', value: String(stats.active_servers), inline: true },
        { name: '🟣 Installing', value: String(stats.installing_servers), inline: true },
        { name: '🔴 Suspended', value: String(stats.suspended_servers), inline: true },
        { name: '🔗 Linked', value: String(stats.linked_discord_users), inline: true },
      ],
      footer: config.panel_name,
      timestamp: true,
    })],
  });
});

cmd('admin-user-list', async (interaction) => {
  if (!requireAdmin(interaction)) return;
  const users = await get('/api/client/bot/users');
  await interaction.reply({
    embeds: [buildEmbed({
      color: 0x3b82f6, title: '👥 All Users',
      fields: users.slice(0, 25).map(u => ({
        name: `${u.root_admin ? '👑 ' : ''}${u.username}`,
        value: `\`${u.id}\` | ${u.email}`,
        inline: true,
      })),
      footer: `${users.length} shown (max 25)`,
      timestamp: true,
    })],
  });
});

cmd('admin-user-create', async (interaction) => {
  if (!requireAdmin(interaction)) return;
  const email = interaction.options.getString('email');
  const username = interaction.options.getString('username');
  const password = interaction.options.getString('password');
  const admin = interaction.options.getBoolean('admin') || false;
  try {
    const res = await post('/api/client/bot/users', { email, username, password, admin });
    await interaction.reply({
      embeds: [buildEmbed({
        color: 0x22c55e, title: '✅ User Created',
        fields: [
          { name: 'ID', value: String(res.id), inline: true },
          { name: 'Username', value: res.username, inline: true },
          { name: 'Email', value: res.email, inline: true },
          { name: 'Admin', value: admin ? 'Yes' : 'No', inline: true },
        ],
        timestamp: true,
      })],
    });
  } catch (e) {
    await interaction.reply({ embeds: [{ color: 0xef4444, description: `❌ ${e.response?.data?.error || e.message}` }], ephemeral: true });
  }
});

cmd('admin-user-delete', async (interaction) => {
  if (!requireAdmin(interaction)) return;
  const id = interaction.options.getString('user');
  try {
    await del(`/api/client/bot/users/${id}`);
    await interaction.reply({ embeds: [{ color: 0x22c55e, description: `✅ User \`${id}\` deleted.` }] });
  } catch (e) {
    await interaction.reply({ embeds: [{ color: 0xef4444, description: `❌ ${e.response?.data?.error || 'Failed'}` }], ephemeral: true });
  }
});

cmd('admin-user-suspend', async (interaction) => {
  if (!requireAdmin(interaction)) return;
  const id = interaction.options.getString('user');
  try {
    await post(`/api/client/bot/users/${id}/action`, { action: 'suspend' });
    await interaction.reply({ embeds: [{ color: 0xf59e0b, description: `⏸️ User \`${id}\` suspended.` }] });
  } catch (e) {
    await interaction.reply({ embeds: [{ color: 0xef4444, description: `❌ ${e.response?.data?.error || 'Failed'}` }], ephemeral: true });
  }
});

cmd('admin-user-unsuspend', async (interaction) => {
  if (!requireAdmin(interaction)) return;
  const id = interaction.options.getString('user');
  try {
    await post(`/api/client/bot/users/${id}/action`, { action: 'unsuspend' });
    await interaction.reply({ embeds: [{ color: 0x22c55e, description: `✅ User \`${id}\` unsuspended.` }] });
  } catch (e) {
    await interaction.reply({ embeds: [{ color: 0xef4444, description: `❌ ${e.response?.data?.error || 'Failed'}` }], ephemeral: true });
  }
});

cmd('admin-user-update', async (interaction) => {
  if (!requireAdmin(interaction)) return;
  const id = interaction.options.getString('user');
  const body = {};
  const email = interaction.options.getString('email');
  const username = interaction.options.getString('username');
  const password = interaction.options.getString('password');
  const admin = interaction.options.getBoolean('admin');
  if (email !== null) body.email = email;
  if (username !== null) body.username = username;
  if (password !== null) body.password = password;
  if (admin !== null) body.admin = admin;
  if (!Object.keys(body).length) return interaction.reply({ embeds: [{ color: 0xef4444, description: '❌ Provide at least one field.' }], ephemeral: true });
  try {
    await patch(`/api/client/bot/users/${id}`, body);
    await interaction.reply({ embeds: [{ color: 0x22c55e, description: `✅ User \`${id}\` updated.` }] });
  } catch (e) {
    await interaction.reply({ embeds: [{ color: 0xef4444, description: `❌ ${e.response?.data?.error || 'Failed'}` }], ephemeral: true });
  }
});

cmd('admin-server-list', async (interaction) => {
  if (!requireAdmin(interaction)) return;
  const servers = await get('/api/client/bot/servers?all=true');
  await interaction.reply({
    embeds: [buildEmbed({
      color: 0x3b82f6, title: '🖥️ All Servers',
      fields: servers.slice(0, 25).map(s => ({
        name: `${statusEmoji(s.status)} ${s.name}`,
        value: `\`${s.uuidShort}\` | Owner: \`${s.owner_id}\``,
        inline: true,
      })),
      footer: `${servers.length} total`,
      timestamp: true,
    })],
  });
});

cmd('admin-server-action', async (interaction) => {
  if (!requireAdmin(interaction)) return;
  const id = interaction.options.getString('server');
  const action = interaction.options.getString('action');
  try {
    await post(`/api/client/bot/servers/${id}/action`, { action });
    await interaction.reply({ embeds: [{ color: 0x22c55e, description: `✅ Server \`${id}\`: ${action}` }] });
  } catch (e) {
    await interaction.reply({ embeds: [{ color: 0xef4444, description: `❌ ${e.response?.data?.error || 'Failed'}` }], ephemeral: true });
  }
});

cmd('admin-server-limits', async (interaction) => {
  if (!requireAdmin(interaction)) return;
  const id = interaction.options.getString('server');
  const body = {};
  for (const key of ['memory', 'swap', 'disk', 'io', 'cpu', 'database_limit', 'allocation_limit', 'backup_limit']) {
    const val = interaction.options.getInteger(key);
    if (val !== null) body[key] = val;
  }
  const threads = interaction.options.getString('threads');
  if (threads !== null) body.threads = threads;
  if (!Object.keys(body).length) return interaction.reply({ embeds: [{ color: 0xef4444, description: '❌ Provide at least one limit.' }], ephemeral: true });
  try {
    await patch(`/api/client/bot/servers/${id}/limits`, body);
    await interaction.reply({ embeds: [{ color: 0x22c55e, description: `✅ Limits updated for \`${id}\`` }] });
  } catch (e) {
    await interaction.reply({ embeds: [{ color: 0xef4444, description: `❌ ${e.response?.data?.error || 'Failed'}` }], ephemeral: true });
  }
});

cmd('admin-node-list', async (interaction) => {
  if (!requireAdmin(interaction)) return;
  const nodes = await get('/api/client/bot/nodes');
  await interaction.reply({
    embeds: [buildEmbed({
      color: 0x3b82f6, title: '🌐 Nodes',
      fields: nodes.map(n => ({
        name: n.name,
        value: `\`${n.id}\` | ${n.fqdn} | 💾 ${n.memory}MB | 💿 ${n.disk}MB | 🖥️ ${n.servers_count} servers`,
      })),
      timestamp: true,
    })],
  });
});

cmd('admin-egg-list', async (interaction) => {
  if (!requireAdmin(interaction)) return;
  const nestId = interaction.options.getString('nest');
  const eggs = await get(`/api/client/bot/eggs${nestId ? `?nest_id=${nestId}` : ''}`);
  await interaction.reply({
    embeds: [buildEmbed({
      color: 0x3b82f6, title: '🥚 Eggs',
      fields: eggs.slice(0, 25).map(e => ({
        name: e.name, value: `Nest: \`${e.nest_id}\` | ID: \`${e.id}\``, inline: true,
      })),
      footer: `${eggs.length} total`,
      timestamp: true,
    })],
  });
});

cmd('admin-nest-list', async (interaction) => {
  if (!requireAdmin(interaction)) return;
  const nests = await get('/api/client/bot/nests');
  await interaction.reply({
    embeds: [buildEmbed({
      color: 0x3b82f6, title: '🏠 Nests',
      fields: nests.map(n => ({
        name: n.name, value: `\`${n.id}\` | ${n.eggs_count} eggs`, inline: true,
      })),
      timestamp: true,
    })],
  });
});

cmd('admin-location-list', async (interaction) => {
  if (!requireAdmin(interaction)) return;
  const locs = await get('/api/client/bot/locations');
  await interaction.reply({
    embeds: [buildEmbed({
      color: 0x3b82f6, title: '📍 Locations',
      fields: locs.map(l => ({
        name: l.short, value: `${l.long} | \`${l.id}\``, inline: true,
      })),
      timestamp: true,
    })],
  });
});

// ─── AUTOCOMPLETE ──────────────────────────────────────────

const autocompleteHandlers = {
  server: async (interaction) => {
    const focused = interaction.options.getFocused();
    const servers = await get(`/api/client/bot/servers?discord_id=${interaction.user.id}`);
    const filtered = servers.filter(s => s.name.toLowerCase().includes(focused.toLowerCase()));
    await interaction.respond(filtered.slice(0, 25).map(s => ({ name: `${s.name} (${s.uuidShort})`, value: s.uuidShort })));
  },
  admin_server: async (interaction) => {
    const focused = interaction.options.getFocused();
    const servers = await get('/api/client/bot/servers?all=true');
    const filtered = servers.filter(s => s.name.toLowerCase().includes(focused.toLowerCase()) || s.uuidShort.includes(focused));
    await interaction.respond(filtered.slice(0, 25).map(s => ({ name: `${s.name} (${s.uuidShort})`, value: String(s.uuidShort) })));
  },
  user: async (interaction) => {
    const focused = interaction.options.getFocused();
    const users = await get(`/api/client/bot/users?q=${encodeURIComponent(focused)}`);
    await interaction.respond(users.slice(0, 25).map(u => ({ name: `${u.username} (${u.email})`, value: String(u.id) })));
  },
  nest: async (interaction) => {
    const focused = interaction.options.getFocused();
    const nests = await get('/api/client/bot/nests');
    const filtered = nests.filter(n => n.name.toLowerCase().includes(focused.toLowerCase()));
    await interaction.respond(filtered.slice(0, 25).map(n => ({ name: n.name, value: String(n.id) })));
  },
};

// ─── COMMAND DEFINITIONS ───────────────────────────────────

const commandDefinitions = [
  // Link
  { name: 'link', description: 'Link your Discord account to the panel',
    options: [{ type: 3, name: 'code', description: '6-digit code from panel settings', required: true }] },
  { name: 'unlink', description: 'Unlink your Discord account' },
  { name: 'status', description: 'Check your linked account status' },

  // User Server
  { name: 'my-servers', description: 'List your servers' },
  { name: 'server-info', description: 'Get detailed server info',
    options: [{ type: 3, name: 'server', description: 'Server name or UUID', required: true, autocomplete: true }] },
  { name: 'server-power', description: 'Send power action to a server',
    options: [
      { type: 3, name: 'server', description: 'Server', required: true, autocomplete: true },
      { type: 3, name: 'action', description: 'Power action', required: true,
        choices: [{ name: 'Start', value: 'start' }, { name: 'Stop', value: 'stop' },
          { name: 'Restart', value: 'restart' }, { name: 'Kill', value: 'kill' }] },
    ] },
  { name: 'server-command', description: 'Send a console command to a server',
    options: [
      { type: 3, name: 'server', description: 'Server', required: true, autocomplete: true },
      { type: 3, name: 'command', description: 'Command to execute', required: true },
    ] },

  // Admin User
  { name: 'admin-stats', description: '[Admin] View panel statistics' },
  { name: 'admin-user-list', description: '[Admin] List all users' },
  { name: 'admin-user-create', description: '[Admin] Create a new user',
    options: [
      { type: 3, name: 'email', description: 'Email address', required: true },
      { type: 3, name: 'username', description: 'Username', required: true },
      { type: 3, name: 'password', description: 'Password', required: true },
      { type: 5, name: 'admin', description: 'Make admin?', required: false },
    ] },
  { name: 'admin-user-update', description: '[Admin] Update a user',
    options: [
      { type: 3, name: 'user', description: 'User ID', required: true, autocomplete: true },
      { type: 3, name: 'email', description: 'New email', required: false },
      { type: 3, name: 'username', description: 'New username', required: false },
      { type: 3, name: 'password', description: 'New password', required: false },
      { type: 5, name: 'admin', description: 'Admin status', required: false },
    ] },
  { name: 'admin-user-delete', description: '[Admin] Delete a user',
    options: [{ type: 3, name: 'user', description: 'User ID', required: true, autocomplete: true }] },
  { name: 'admin-user-suspend', description: '[Admin] Suspend a user\'s servers',
    options: [{ type: 3, name: 'user', description: 'User ID', required: true, autocomplete: true }] },
  { name: 'admin-user-unsuspend', description: '[Admin] Unsuspend a user\'s servers',
    options: [{ type: 3, name: 'user', description: 'User ID', required: true, autocomplete: true }] },

  // Admin Server
  { name: 'admin-server-list', description: '[Admin] List all servers' },
  { name: 'admin-server-action', description: '[Admin] Delete/suspend/unsuspend/reinstall',
    options: [
      { type: 3, name: 'server', description: 'Server UUID', required: true, autocomplete: true },
      { type: 3, name: 'action', description: 'Action', required: true,
        choices: [{ name: 'Suspend', value: 'suspend' }, { name: 'Unsuspend', value: 'unsuspend' },
          { name: 'Delete', value: 'delete' }, { name: 'Reinstall', value: 'reinstall' }] },
    ] },
  { name: 'admin-server-limits', description: '[Admin] Update server resource limits',
    options: [
      { type: 3, name: 'server', description: 'Server UUID', required: true, autocomplete: true },
      { type: 4, name: 'memory', description: 'Memory in MB', required: false },
      { type: 4, name: 'swap', description: 'Swap in MB', required: false },
      { type: 4, name: 'disk', description: 'Disk in MB', required: false },
      { type: 4, name: 'io', description: 'IO weight (10-1000)', required: false },
      { type: 4, name: 'cpu', description: 'CPU limit %', required: false },
      { type: 3, name: 'threads', description: 'CPU threads e.g. 0-3', required: false },
      { type: 4, name: 'database_limit', description: 'Database limit', required: false },
      { type: 4, name: 'allocation_limit', description: 'Allocation limit', required: false },
      { type: 4, name: 'backup_limit', description: 'Backup limit', required: false },
    ] },

  // Admin Resources
  { name: 'admin-node-list', description: '[Admin] List all nodes' },
  { name: 'admin-egg-list', description: '[Admin] List all eggs',
    options: [{ type: 3, name: 'nest', description: 'Filter by nest', required: false, autocomplete: true }] },
  { name: 'admin-nest-list', description: '[Admin] List all nests' },
  { name: 'admin-location-list', description: '[Admin] List all locations' },
];

// ─── CLIENT HANDLERS ───────────────────────────────────────

client.on('ready', async () => {
  console.log(`✅ Logged in as ${client.user.tag}`);
  const rest = new REST({ version: '10' }).setToken(config.discord_bot_token);
  try {
    await rest.put(Routes.applicationGuildCommands(client.user.id, config.discord_guild_id), { body: commandDefinitions });
    console.log(`✅ Registered ${commandDefinitions.length} commands for guild ${config.discord_guild_id}`);
  } catch (e) {
    console.error('❌ Failed to register commands:', e.message);
  }
});

client.on('interactionCreate', async (interaction) => {
  if (interaction.isAutocomplete()) {
    const handler = autocompleteHandlers[interaction.commandName];
    if (handler) try { await handler(interaction); } catch {}
    return;
  }
  if (!interaction.isChatInputCommand()) return;

  const handler = commands.get(interaction.commandName);
  if (!handler) return;

  try {
    await handler(interaction);
  } catch (e) {
    console.error(`Error in ${interaction.commandName}:`, e.message);
    if (!interaction.replied && !interaction.deferred) {
      await interaction.reply({ embeds: [{ color: 0xef4444, description: '❌ An unexpected error occurred.' }], ephemeral: true });
    }
  }
});

client.login(config.discord_bot_token);
