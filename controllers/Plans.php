<?php namespace Aero\Clouds\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Flash;

class Plans extends Controller
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
    //     'aero.clouds.access_plans'
    // ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aero.Clouds', 'clouds', 'plans');
    }

    public function listInjectRowClass($record, $definition = null)
    {
        if (!$record->is_active) {
            return 'strike';
        }
    }

    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
            foreach ($checkedIds as $recordId) {
                if (!$record = \Aero\Clouds\Models\Plan::find($recordId)) {
                    continue;
                }
                $record->delete();
            }

            Flash::success('Successfully deleted selected plans');
        }

        return $this->listRefresh();
    }

    public function index_onBulkAction()
    {
        if (($bulkAction = post('action')) && ($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $objectId) {
                if (!$object = \Aero\Clouds\Models\Plan::find($objectId)) {
                    continue;
                }

                switch ($bulkAction) {
                    case 'delete':
                        $object->delete();
                        break;
                }
            }

            Flash::success('Successfully processed ' . count($checkedIds) . ' records.');
        }

        return $this->listRefresh();
    }
}