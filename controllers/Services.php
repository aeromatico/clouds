<?php namespace Aero\Clouds\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Services extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\ReorderController'
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    // public $requiredPermissions = [
    //     'aero.clouds.access_services'
    // ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aero.Clouds', 'clouds', 'services');
    }

    public function test()
    {
        return 'Clouds Services Controller is working!';
    }

    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
            foreach ($checkedIds as $recordId) {
                if (!$record = \Aero\Clouds\Models\Service::find($recordId)) {
                    continue;
                }
                $record->delete();
            }

            \Flash::success('Successfully deleted selected services');
        }

        return $this->listRefresh();
    }
}