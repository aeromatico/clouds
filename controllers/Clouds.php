<?php namespace Aero\Clouds\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Aero\Clouds\Models\Cloud;
use Flash;

class Clouds extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController'
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aero.Clouds', 'commerce', 'cloudservers');
    }

    /**
     * Activate a cloud server
     */
    public function onActivate()
    {
        $id = post('id');
        $cloud = Cloud::find($id);

        if (!$cloud) {
            Flash::error('Server not found');
            return;
        }

        $cloud->activate();
        Flash::success('Server activated successfully');

        return $this->listRefresh();
    }

    /**
     * Suspend a cloud server
     */
    public function onSuspend()
    {
        $id = post('id');
        $reason = post('reason', 'Administrative suspension');
        $cloud = Cloud::find($id);

        if (!$cloud) {
            Flash::error('Server not found');
            return;
        }

        $cloud->suspend($reason);
        Flash::success('Server suspended successfully');

        return $this->listRefresh();
    }

    /**
     * Reactivate a suspended server
     */
    public function onReactivate()
    {
        $id = post('id');
        $cloud = Cloud::find($id);

        if (!$cloud) {
            Flash::error('Server not found');
            return;
        }

        $cloud->reactivate();
        Flash::success('Server reactivated successfully');

        return $this->listRefresh();
    }

    /**
     * Terminate a cloud server
     */
    public function onTerminate()
    {
        $id = post('id');
        $reason = post('reason', 'Terminated by administrator');
        $cloud = Cloud::find($id);

        if (!$cloud) {
            Flash::error('Server not found');
            return;
        }

        $cloud->terminate($reason);
        Flash::success('Server terminated successfully');

        return $this->listRefresh();
    }

    /**
     * Renew a cloud server
     */
    public function onRenew()
    {
        $id = post('id');
        $months = post('months', 1);
        $cloud = Cloud::find($id);

        if (!$cloud) {
            Flash::error('Server not found');
            return;
        }

        $cloud->renew($months);
        Flash::success("Server renewed for {$months} month(s)");

        return $this->listRefresh();
    }
}
