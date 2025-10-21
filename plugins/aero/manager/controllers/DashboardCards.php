<?php namespace Aero\Manager\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class DashboardCards extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'Dashboard' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aero.Manager', 'aero_manager_services', 'aero_manager_plans');
    }
}
