<?php namespace Aero\Clouds\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Aero\Clouds\Models\ActivityLog;

class ActivityLogs extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController'
    ];

    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aero.Clouds', 'clouds', 'activitylogs');
    }

    /**
     * Delete old logs
     */
    public function onDeleteOldLogs()
    {
        $days = post('days', 30);
        $deleted = ActivityLog::where('created_at', '<', now()->subDays($days))->delete();

        \Flash::success("Successfully deleted {$deleted} log entries older than {$days} days.");
        return $this->listRefresh();
    }

    /**
     * Clear all logs
     */
    public function onClearAllLogs()
    {
        $count = ActivityLog::count();
        ActivityLog::truncate();

        \Flash::success("Successfully cleared all {$count} log entries.");
        return $this->listRefresh();
    }
}
