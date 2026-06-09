<?php

namespace Pterodactyl\Extensions\Themes;

use Pterodactyl\Services\Helpers\AssetHashService;

class Theme
{
    protected AssetHashService $assets;

    public function __construct()
    {
        $this->assets = app(AssetHashService::class);
    }

    public function css(string $resource): string
    {
        return $this->assets->css($this->processCacheVersion($resource));
    }

    public function js(string $resource): string
    {
        return $this->assets->js($this->processCacheVersion($resource));
    }

    public function url(string $resource): string
    {
        return $this->assets->url($this->processCacheVersion($resource));
    }

    protected function processCacheVersion(string $resource): string
    {
        return str_replace('{cache-version}', time(), $resource);
    }
}
