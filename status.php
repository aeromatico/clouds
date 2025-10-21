<?php
// Status check for Master Theme
header('Content-Type: application/json');

$status = [
    'timestamp' => date('Y-m-d H:i:s'),
    'server' => 'Running ✅',
    'php_version' => PHP_VERSION,
    'theme' => 'master',
    'database' => 'connected',
    'pages' => []
];

// Test database
try {
    $pdo = new PDO("mysql:host=localhost;dbname=master", "master", "TMeeWx0F7YDUqsN16nDl");
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM system_settings");
    $result = $stmt->fetch();
    $status['database'] = "Connected ✅ ({$result['count']} settings)";
} catch (Exception $e) {
    $status['database'] = "Error: " . $e->getMessage();
}

// Check pages
$pages = [
    'home' => 'themes/master/pages/home.htm',
    'about' => 'themes/master/pages/about.htm',
];

foreach ($pages as $name => $path) {
    $status['pages'][$name] = file_exists($path) ? '✅ Ready' : '❌ Missing';
}

// Check assets
$status['assets'] = [
    'vite_config' => file_exists('themes/master/vite.config.js') ? '✅' : '❌',
    'tailwind_config' => file_exists('themes/master/tailwind.config.js') ? '✅' : '❌',
    'package_json' => file_exists('themes/master/package.json') ? '✅' : '❌',
    'built_assets' => file_exists('themes/master/assets/dist') ? '✅' : '❌',
];

// Check theme files
$status['theme_files'] = [
    'layout_default' => file_exists('themes/master/layouts/default.htm') ? '✅' : '❌',
    'navigation' => file_exists('themes/master/partials/navigation.htm') ? '✅' : '❌',
    'footer' => file_exists('themes/master/partials/footer.htm') ? '✅' : '❌',
    'theme_config' => file_exists('themes/master/theme.yaml') ? '✅' : '❌',
];

// Check plugin
$status['vite_plugin'] = file_exists('plugins/aero/vite/Plugin.php') ? '✅ Installed' : '❌ Missing';

// URLs to test
$status['test_urls'] = [
    'home' => 'http://localhost:8000/',
    'about' => 'http://localhost:8000/about',
    'status' => 'http://localhost:8000/status.php'
];

echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>