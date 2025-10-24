<?php namespace Aero\Clouds\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Aero\Clouds\Models\DomainProvider;
use Flash;
use Exception;

class DomainProviders extends Controller
{
    public $implement = [
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\FormController::class
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    // public $requiredPermissions = [
    //     'aero.clouds.access_domain_providers'
    // ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aero.Clouds', 'clouds', 'domainproviders');
    }

    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
            foreach ($checkedIds as $recordId) {
                if (!$record = DomainProvider::find($recordId)) {
                    continue;
                }
                $record->delete();
            }

            Flash::success('Successfully deleted selected domain providers');
        }

        return $this->listRefresh();
    }

    /**
     * AJAX handler: Sync pricing from registrar
     */
    public function update_onSyncPricing($recordId)
    {
        try {
            $provider = DomainProvider::findOrFail($recordId);

            // Get the appropriate registrar
            $registrar = $this->getRegistrar($provider);

            if (!$registrar) {
                throw new Exception("No registrar adapter available for {$provider->provider_type}");
            }

            // Test connection first
            if (!$registrar->testConnection()) {
                throw new Exception("Connection failed: " . ($registrar->getLastError() ?? 'Unknown error'));
            }

            // Sync pricing
            $result = $registrar->syncPricing();

            if ($result['success']) {
                $message = "Successfully synced {$result['synced']} extension(s)";

                if (!empty($result['errors'])) {
                    $message .= " with " . count($result['errors']) . " error(s). Check logs for details.";
                    Flash::warning($message);
                } else {
                    Flash::success($message);
                }
            } else {
                throw new Exception($registrar->getLastError() ?? 'Sync failed');
            }
        } catch (Exception $e) {
            Flash::error("Error: {$e->getMessage()}");
        }
    }

    /**
     * AJAX handler: Test registrar connection
     */
    public function update_onTestConnection($recordId)
    {
        try {
            $provider = DomainProvider::findOrFail($recordId);

            // Get the appropriate registrar
            $registrar = $this->getRegistrar($provider);

            if (!$registrar) {
                throw new Exception("No registrar adapter available for {$provider->provider_type}");
            }

            // Test connection
            if ($registrar->testConnection()) {
                Flash::success("Connection successful! API credentials are valid.");
            } else {
                $error = $registrar->getLastError() ?? 'Connection failed';
                \Log::error("Dynadot connection failed for provider {$provider->id}: {$error}");
                throw new Exception($error);
            }
        } catch (Exception $e) {
            \Log::error("Dynadot test connection error: " . $e->getMessage());
            Flash::error("Connection failed: {$e->getMessage()}");
        }
    }

    /**
     * Get registrar adapter for provider
     *
     * @param DomainProvider $provider
     * @return object|null
     */
    protected function getRegistrar(DomainProvider $provider): ?object
    {
        switch ($provider->provider_type) {
            case 'dynadot':
                return new \Aero\Clouds\Classes\Registrars\DynadotRegistrar($provider);

            // Add more registrars here in the future
            // case 'namecheap':
            //     return new \Aero\Clouds\Classes\Registrars\NamecheapRegistrar($provider);

            default:
                return null;
        }
    }
}
