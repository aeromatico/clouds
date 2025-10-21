<?php namespace Aero\Test\Controllers;

use Backend;
use BackendMenu;
use Backend\Classes\Controller;

class Students extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = [
        'test_students' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aero.Test', 'students', 'students_students');
    }

}
