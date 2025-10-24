<?php namespace Aero\Clouds\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Aero\Clouds\Models\SupportDepartment;
use Flash;

class SupportDepartments extends Controller
{
    public $implement = [
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\FormController::class
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = ['aero.clouds.support'];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aero.Clouds', 'clouds-support', 'support-departments');
    }
}
