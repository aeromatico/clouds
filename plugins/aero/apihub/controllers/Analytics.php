<?php namespace Aero\ApiHub\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Aero\ApiHub\Models\Api;
use Aero\ApiHub\Models\Endpoint;

/**
 * Analytics Backend Controller
 */
class Analytics extends Controller
{
    /**
     * @var array required permissions
     */
    public $requiredPermissions = ['aero.apihub.access_analytics'];

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aero.ApiHub', 'apihub', 'analytics');
    }

    /**
     * Index action
     */
    public function index()
    {
        $this->pageTitle = 'Analytics Dashboard';

        // Get statistics
        $stats = Api::getStats();
        $methodStats = Endpoint::getMethodStats();
        $commonPatterns = Endpoint::getCommonPatterns();

        // Get category chart data
        $categoryData = [
            'labels' => array_keys($stats['by_category']),
            'data' => array_values($stats['by_category']),
        ];

        // Get method chart data
        $methodData = [
            'labels' => array_keys($methodStats),
            'data' => array_values($methodStats),
            'colors' => $this->getMethodColors(array_keys($methodStats)),
        ];

        $this->vars['stats'] = $stats;
        $this->vars['categoryData'] = $categoryData;
        $this->vars['methodData'] = $methodData;
        $this->vars['commonPatterns'] = $commonPatterns;
    }

    /**
     * Get colors for methods
     */
    protected function getMethodColors($methods)
    {
        $colorMap = [
            'GET' => '#28a745',
            'POST' => '#007bff',
            'PUT' => '#ffc107',
            'PATCH' => '#fd7e14',
            'DELETE' => '#dc3545',
            'HEAD' => '#6c757d',
            'OPTIONS' => '#17a2b8',
        ];

        return array_map(function ($method) use ($colorMap) {
            return $colorMap[$method] ?? '#6c757d';
        }, $methods);
    }

    /**
     * Export statistics as JSON
     */
    public function export()
    {
        $stats = [
            'overview' => Api::getStats(),
            'methods' => Endpoint::getMethodStats(),
            'patterns' => Endpoint::getCommonPatterns(),
            'generated_at' => now()->toIso8601String(),
        ];

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="apihub-stats-' . date('Y-m-d') . '.json"');
        echo json_encode($stats, JSON_PRETTY_PRINT);
        exit;
    }
}
