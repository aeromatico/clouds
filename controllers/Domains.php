<?php namespace Aero\Clouds\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Aero\Clouds\Models\Domain;
use Flash;

class Domains extends Controller
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
        BackendMenu::setContext('Aero.Clouds', 'commerce', 'domains');
    }

    /**
     * Extend the query to eager load relationships
     */
    public function listExtendQuery($query)
    {
        $query->with(['user', 'extension', 'provider', 'order']);
    }

    /**
     * Activate a domain
     */
    public function onActivate()
    {
        $id = post('id');
        $domain = Domain::find($id);

        if (!$domain) {
            Flash::error('Domain not found');
            return;
        }

        $domain->status = 'active';
        $domain->save();

        Flash::success('Domain activated successfully');
        return $this->listRefresh();
    }

    /**
     * Suspend a domain
     */
    public function onSuspend()
    {
        $id = post('id');
        $domain = Domain::find($id);

        if (!$domain) {
            Flash::error('Domain not found');
            return;
        }

        $domain->status = 'suspended';
        $domain->save();

        Flash::success('Domain suspended successfully');
        return $this->listRefresh();
    }

    /**
     * Cancel a domain
     */
    public function onCancel()
    {
        $id = post('id');
        $domain = Domain::find($id);

        if (!$domain) {
            Flash::error('Domain not found');
            return;
        }

        $domain->status = 'cancelled';
        $domain->save();

        Flash::success('Domain cancelled successfully');
        return $this->listRefresh();
    }

    /**
     * Sync nameservers from registrar
     */
    public function onSyncNameservers()
    {
        $id = post('id');
        $domain = Domain::find($id);

        if (!$domain) {
            Flash::error('Domain not found');
            return;
        }

        try {
            $result = $domain->syncNameservers();

            if ($result['success']) {
                Flash::success($result['message']);
            } else {
                Flash::error($result['message']);
            }
        } catch (\Exception $e) {
            Flash::error('Error syncing nameservers: ' . $e->getMessage());
        }

        return $this->listRefresh();
    }

    /**
     * Sync DNS records from registrar
     */
    public function onSyncDns()
    {
        $id = post('id');
        $domain = Domain::find($id);

        if (!$domain) {
            Flash::error('Domain not found');
            return;
        }

        try {
            $result = $domain->syncDnsRecords();

            if ($result['success']) {
                Flash::success($result['message']);
            } else {
                Flash::error($result['message']);
            }
        } catch (\Exception $e) {
            Flash::error('Error syncing DNS records: ' . $e->getMessage());
        }

        return $this->listRefresh();
    }

    /**
     * Lock domain
     */
    public function onLock()
    {
        $id = post('id');
        $domain = Domain::find($id);

        if (!$domain) {
            Flash::error('Domain not found');
            return;
        }

        try {
            $result = $domain->lock();

            if ($result['success']) {
                Flash::success($result['message']);
            } else {
                Flash::error($result['message']);
            }
        } catch (\Exception $e) {
            Flash::error('Error locking domain: ' . $e->getMessage());
        }

        return $this->listRefresh();
    }

    /**
     * Unlock domain
     */
    public function onUnlock()
    {
        $id = post('id');
        $domain = Domain::find($id);

        if (!$domain) {
            Flash::error('Domain not found');
            return;
        }

        try {
            $result = $domain->unlock();

            if ($result['success']) {
                Flash::success($result['message']);
            } else {
                Flash::error($result['message']);
            }
        } catch (\Exception $e) {
            Flash::error('Error unlocking domain: ' . $e->getMessage());
        }

        return $this->listRefresh();
    }

    /**
     * Get EPP/Auth code
     */
    public function onGetAuthCode()
    {
        $id = post('id');
        $domain = Domain::find($id);

        if (!$domain) {
            Flash::error('Domain not found');
            return;
        }

        try {
            $result = $domain->getAuthCode();

            if ($result['success']) {
                Flash::success('Auth Code: ' . $result['auth_code']);
            } else {
                Flash::error($result['message']);
            }
        } catch (\Exception $e) {
            Flash::error('Error getting auth code: ' . $e->getMessage());
        }

        return $this->listRefresh();
    }

    /**
     * Delete selected domains
     */
    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
            foreach ($checkedIds as $recordId) {
                if (!$record = Domain::find($recordId)) {
                    continue;
                }
                $record->delete();
            }

            Flash::success('Successfully deleted selected domains');
        }

        return $this->listRefresh();
    }
}
