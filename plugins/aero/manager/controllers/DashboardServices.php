<?php namespace Aero\Manager\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class DashboardServices extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'DashboardServices' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aero.Manager', 'aero_manager_orders', 'aero_manager_dashboard_services');
    }
}
