@php
/*
 * Custom Vite asset loader for non-standard XAMPP sub-folder install.
 * Reads the manifest from the root build/ directory and outputs
 * properly prefixed asset URLs using APP_URL as base.
 */
$_viteBase = rtrim(config('app.url'), '/');
$_viteManifest = json_decode(file_get_contents(base_path('build/manifest.json')), true) ?? [];

$_viteAssets = is_array($__viteSources ?? null)
    ? $__viteSources
    : (isset($__viteSources) ? [$__viteSources] : []);
@endphp

@foreach($_viteAssets as $_viteEntry)
    @php
        $_viteKey = ltrim($_viteEntry, '/');
        $_viteFile = $_viteManifest[$_viteKey]['file'] ?? null;
    @endphp
    @if($_viteFile)
        @if(str_ends_with($_viteFile, '.css'))
            <link rel="stylesheet" href="{{ $_viteBase }}/build/{{ $_viteFile }}">
        @else
            <script type="module" src="{{ $_viteBase }}/build/{{ $_viteFile }}"></script>
        @endif
    @endif
@endforeach
