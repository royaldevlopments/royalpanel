{{-- PWA Manifest --}}
{
    "name": "{{ $siteConfiguration['royal']['meta_title'] ?? 'Royal Panel' }}",
    "short_name": "Royal Panel",
    "description": "{{ $siteConfiguration['royal']['meta_description'] ?? 'Game server management panel' }}",
    "start_url": "/",
    "display": "standalone",
    "background_color": "{{ $siteConfiguration['royal']['meta_color'] ?? '#0e0e1a' }}",
    "theme_color": "{{ $siteConfiguration['royal']['meta_color'] ?? '#0e0e1a' }}",
    "orientation": "portrait-primary",
    "icons": [
        {
            "src": "{{ $siteConfiguration['royal']['meta_favicon'] ?? '/favicons/android-chrome-192x192.png' }}",
            "sizes": "192x192",
            "type": "image/png"
        }
    ]
}