<?php namespace Aero\Clouds\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

/**
 * Email Events Controller
 *
 * Gestiona la configuración de eventos de correo electrónico
 */
class EmailEvents extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aero.Clouds', 'clouds-setup', 'email-events');
    }

    /**
     * Custom list row class
     */
    public function listInjectRowClass($record, $definition = null)
    {
        if (!$record->enabled) {
            return 'safe disabled';
        }
    }
}
