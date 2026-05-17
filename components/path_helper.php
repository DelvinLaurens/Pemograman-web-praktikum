<?php
if (!function_exists('appBaseUrl')) {
    function appBaseUrl() {
        static $base_url = null;

        if ($base_url !== null) {
            return $base_url;
        }

        $document_root = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
        $project_root = realpath(dirname(__DIR__));

        if ($document_root && $project_root && strpos($project_root, $document_root) === 0) {
            $relative = trim(str_replace('\\', '/', substr($project_root, strlen($document_root))), '/');
            $base_url = '/' . ($relative !== '' ? $relative . '/' : '');
            return $base_url;
        }

        $script_name = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
        $base_url = preg_replace('#/(admin|auth|pages)/[^/]*$#', '/', $script_name);
        $base_url = preg_replace('#/[^/]*$#', '/', $base_url);

        return $base_url ?: '/';
    }
}

if (!function_exists('url_for')) {
    function url_for($path = '') {
        $path = (string) $path;

        if ($path === '') {
            return appBaseUrl();
        }

        if (preg_match('/^[a-z][a-z0-9+.-]*:/i', $path) || strpos($path, '//') === 0 || strpos($path, '#') === 0) {
            return $path;
        }

        if (strpos($path, '/') === 0) {
            return $path;
        }

        return appBaseUrl() . ltrim($path, '/');
    }
}

if (!function_exists('asset_url')) {
    function asset_url($path) {
        return url_for($path);
    }
}

if (!function_exists('current_page_path')) {
    function current_page_path() {
        $script_name = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $base_url = appBaseUrl();

        if ($base_url !== '/' && strpos($script_name, rtrim($base_url, '/') . '/') === 0) {
            return ltrim(substr($script_name, strlen($base_url)), '/');
        }

        return ltrim(basename($script_name), '/');
    }
}
?>
