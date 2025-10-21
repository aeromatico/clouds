<?php namespace Aero\Manager\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class ExchangeQuotes extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'ExchangeQuotes' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aero.Manager', 'aero_manager_exchange', 'aero_manager_exchanges_quotes');
    }
    
    public function listFilterExtendScopes($filter)
    {
        $filter->addScopes([
            'pendiente' => [
                'label' => 'Show pending',
                'type' => 'checkbox',
                'conditions' => "status = 'Pendiente'"
            ],
            'new_chat' => [
                'label' => 'Show with new chat',
                'type' => 'checkbox',
                'conditions' => 'chat_alert = true'
            ]
        ]);
    }    
}


