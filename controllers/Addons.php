<?php namespace Aero\Clouds\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Addons extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\ReorderController'
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aero.Clouds', 'clouds', 'addons');
    }

    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
            foreach ($checkedIds as $recordId) {
                if (!$record = \Aero\Clouds\Models\Addon::find($recordId)) {
                    continue;
                }
                $record->delete();
            }

            \Flash::success('Successfully deleted selected addons');
        }

        return $this->listRefresh();
    }
}
