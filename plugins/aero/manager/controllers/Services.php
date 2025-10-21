<?php namespace Aero\Manager\Controllers;

use Backend\Classes\Controller;
use Backend\Facades\BackendMenu;

class Services extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController',        'Backend\Behaviors\ReorderController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aero.Manager', 'aero_manager_services', 'aero_manager_services');
    }
    
    public function listExtendQuery($query)
    {
        $query->where('domain',$_SERVER['HTTP_HOST']);
    }
    
    public function reorderExtendQuery($query)
    {
        $query->where('domain',$_SERVER['HTTP_HOST']);
    }    
}
