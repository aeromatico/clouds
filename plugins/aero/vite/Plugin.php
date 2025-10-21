<?php

namespace Aero\Vite;

use System\Classes\PluginBase;
use System\Classes\PluginManager;

/**
 * Vite Plugin for OctoberCMS
 */
class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'Vite Integration',
            'description' => 'Integración de Vite para desarrollo moderno con HMR y build optimizado',
            'author'      => 'aero',
            'icon'        => 'icon-bolt',
            'homepage'    => 'https://clouds.com.bo'
        ];
    }

    public function register()
    {
        // Register Twig functions will be done in boot() method
    }

    public function boot()
    {
        // Boot plugin
    }

    public function registerMarkupTags()
    {
        return [
            'functions' => [
                'vite' => [$this, 'viteAsset'],
                'vite_client' => [$this, 'viteClient'],
                'vite_react_refresh' => [$this, 'viteReactRefresh'],
                'vite_entry_tags' => [$this, 'viteEntryTags'],
                'execution_time' => [$this, 'executionTime'],
                'query_count' => [$this, 'queryCount'],
                'memory_usage' => [$this, 'memoryUsage'],
            ]
        ];
    }

    /**
     * Generate Vite asset URLs
     */
    public function viteAsset($entry, $buildDirectory = 'themes/master/assets/dist')
    {
        $isDev = config('app.debug', false) && $this->isViteDevServerRunning();
        
        if ($isDev) {
            return $this->viteDevAsset($entry);
        }

        return $this->viteProdAsset($entry, $buildDirectory);
    }

    /**
     * Generate Vite client script for development
     */
    public function viteClient()
    {
        if (config('app.debug', false) && $this->isViteDevServerRunning()) {
            return '<script type="module" src="http://clouds.com.bo:3000/@vite/client"></script>';
        }

        return '';
    }

    /**
     * Generate React refresh script for development
     */
    public function viteReactRefresh()
    {
        if (config('app.debug', false) && $this->isViteDevServerRunning()) {
            return '<script type="module">
                import RefreshRuntime from "/vite-dev/@react-refresh"
                RefreshRuntime.injectIntoGlobalHook(window)
                window.$RefreshReg$ = () => {}
                window.$RefreshSig$ = () => (type) => type
                window.__vite_plugin_react_preamble_installed__ = true
            </script>';
        }

        return '';
    }

    /**
     * Check if Vite dev server is running
     */
    private function isViteDevServerRunning()
    {
        // Remove static cache to force fresh check each time during development
        $isRunning = null;

        if ($isRunning === null) {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 1
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);

            $isRunning = @file_get_contents('http://clouds.com.bo:3000', false, $context) !== false;
        }

        return $isRunning;
    }

    /**
     * Get development asset URL
     */
    private function viteDevAsset($entry)
    {
        return "http://clouds.com.bo:3000/src/{$entry}";
    }

    /**
     * Get production asset URL from manifest
     */
    private function viteProdAsset($entry, $buildDirectory)
    {
        static $manifest = null;

        if ($manifest === null) {
            $manifestPath = public_path("{$buildDirectory}/.vite/manifest.json");
            
            if (file_exists($manifestPath)) {
                $manifest = json_decode(file_get_contents($manifestPath), true);
            } else {
                $manifest = [];
            }
        }

        $entryKey = "src/{$entry}";
        
        if (isset($manifest[$entryKey])) {
            $file = $manifest[$entryKey]['file'];
            return url("{$buildDirectory}/{$file}");
        }

        // Fallback if manifest not found or entry not exists
        return url("{$buildDirectory}/{$entry}");
    }

    /**
     * Get all CSS imports for an entry from manifest
     */
    public function viteCssImports($entry, $buildDirectory = 'themes/master/assets/dist')
    {
        $manifestPath = public_path("{$buildDirectory}/.vite/manifest.json");
        
        if (!file_exists($manifestPath)) {
            return [];
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $entryKey = "src/{$entry}";
        $cssFiles = [];

        if (isset($manifest[$entryKey]['css'])) {
            foreach ($manifest[$entryKey]['css'] as $cssFile) {
                $cssFiles[] = url("{$buildDirectory}/{$cssFile}");
            }
        }

        return $cssFiles;
    }

    /**
     * Get preload links for an entry
     */
    public function vitePreloadImports($entry, $buildDirectory = 'themes/master/assets/dist')
    {
        $manifestPath = public_path("{$buildDirectory}/.vite/manifest.json");
        
        if (!file_exists($manifestPath)) {
            return [];
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $entryKey = "src/{$entry}";
        $preloads = [];

        if (isset($manifest[$entryKey]['imports'])) {
            foreach ($manifest[$entryKey]['imports'] as $import) {
                if (isset($manifest[$import])) {
                    $preloads[] = [
                        'href' => url("{$buildDirectory}/{$manifest[$import]['file']}"),
                        'as' => $this->getPreloadAs($manifest[$import]['file'])
                    ];
                }
            }
        }

        return $preloads;
    }

    /**
     * Determine preload 'as' attribute based on file extension
     */
    private function getPreloadAs($file)
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'js':
                return 'script';
            case 'css':
                return 'style';
            case 'woff':
            case 'woff2':
                return 'font';
            case 'png':
            case 'jpg':
            case 'jpeg':
            case 'gif':
            case 'svg':
                return 'image';
            default:
                return 'fetch';
        }
    }

    /**
     * Generate complete Vite tags (CSS and JS) for an entry
     */
    public function viteEntryTags($entry, $buildDirectory = 'themes/master/assets/dist')
    {
        $isDev = config('app.debug', false) && $this->isViteDevServerRunning();
        
        $tags = [];

        if ($isDev) {
            // Development mode - just the main entry
            $tags[] = '<script type="module" src="' . $this->viteDevAsset($entry) . '"></script>';
        } else {
            // Production mode - CSS and JS from manifest
            
            // Add CSS files
            $cssFiles = $this->viteCssImports($entry, $buildDirectory);
            foreach ($cssFiles as $cssFile) {
                $tags[] = '<link rel="stylesheet" href="' . $cssFile . '">';
            }

            // Add preload links
            $preloads = $this->vitePreloadImports($entry, $buildDirectory);
            foreach ($preloads as $preload) {
                $tags[] = '<link rel="preload" href="' . $preload['href'] . '" as="' . $preload['as'] . '"' . 
                         ($preload['as'] === 'font' ? ' crossorigin' : '') . '>';
            }

            // Add main JS file
            $jsUrl = $this->viteProdAsset($entry, $buildDirectory);
            $tags[] = '<script type="module" src="' . $jsUrl . '"></script>';
        }

        return implode("\n    ", $tags);
    }

    /**
     * Get page execution time for performance monitoring
     */
    public function executionTime()
    {
        if (defined('LARAVEL_START')) {
            return round((microtime(true) - LARAVEL_START) * 1000, 2);
        }
        
        return '0';
    }

    /**
     * Get database query count for performance monitoring
     */
    public function queryCount()
    {
        if (app()->bound('db')) {
            return count(app('db')->getQueryLog());
        }
        
        return '0';
    }

    /**
     * Get memory usage for performance monitoring
     */
    public function memoryUsage()
    {
        $bytes = memory_get_usage(true);
        $units = ['B', 'KB', 'MB', 'GB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}