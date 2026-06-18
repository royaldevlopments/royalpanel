<?php

namespace RoyalPanel\Extensions\Themes;

class Theme
{
    public function css(string $resource): string
    {
        return '<link rel="stylesheet" href="' . $this->url($resource) . '">';
    }

    public function js(string $resource): string
    {
        return '<script src="' . $this->url($resource) . '"></script>';
    }

    public function url(string $resource): string
    {
        return url(str_replace('{cache-version}', time(), $resource));
    }
}
