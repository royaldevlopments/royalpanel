<?php

namespace RoyalPanel\Http\Controllers\Admin\Extensions\royalfeaturetoggle;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use RoyalPanel\Http\Controllers\Controller;

class royalfeaturetoggleExtensionController extends Controller
{
    public function index(): View
    {
        // Get feature flags from settings
        $features = \RoyalPanel\RoyalAtelier\Models\RxExtension::where('extension_id', 'royalfeaturetoggle')
            ->first()?->getSettings() ?? [];

        return view('admin.extensions.royalfeaturetoggle.index', [
            'features' => $features,
            'root' => "/admin/extensions/royalfeaturetoggle",
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'features' => 'sometimes|array',
            'features.*' => 'string',
        ]);

        // Save feature flags
        $extension = \RoyalPanel\RoyalAtelier\Models\RxExtension::firstOrCreate(
            ['extension_id' => 'royalfeaturetoggle'],
            [
                'name' => 'Royal Feature Toggle',
                'version' => '1.0.0',
                'author' => 'Royal Devlopments',
                'description' => 'Toggle Royal Atelier features on/off from the admin panel.',
                'installed' => true,
                'enabled' => true,
            ]
        );

        $features = $request->input('features', []);
        $extension->setSetting('features', $features);

        return redirect()
            ->route('admin.extensions.royalfeaturetoggle.index')
            ->with('success', 'Feature toggles updated successfully!');
    }
}