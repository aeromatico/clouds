<?php namespace Aero\ApiHub\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Aero\ApiHub\Models\Api;
use Flash;

/**
 * Apis Backend Controller
 */
class Apis extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\RelationController::class,
    ];

    /**
     * @var string formConfig file
     */
    public $formConfig = 'config_form.yaml';

    /**
     * @var string listConfig file
     */
    public $listConfig = 'config_list.yaml';

    /**
     * @var string relationConfig file
     */
    public $relationConfig = 'config_relation.yaml';

    /**
     * @var array required permissions
     */
    public $requiredPermissions = ['aero.apihub.access_apis'];

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Aero.ApiHub', 'apihub', 'apis');
    }

    /**
     * Custom action: Sync API from RapidAPI
     */
    public function onSync()
    {
        $apiId = post('id');
        $api = Api::find($apiId);

        if (!$api || !$api->rapidapi_id) {
            Flash::error('API not found or missing RapidAPI ID');
            return;
        }

        try {
            $client = new \Aero\ApiHub\Classes\RapidApiClient();

            // Get API details
            $result = $client->getApiDetails($api->rapidapi_id);

            if (!$result) {
                Flash::error('Failed to fetch API details from RapidAPI');
                return;
            }

            $apiData = $result['api'] ?? null;
            if (!$apiData) {
                Flash::error('API not found on RapidAPI');
                return;
            }

            // Update API
            $api->name = $apiData['name'];
            $api->description = $apiData['description'] ?? null;
            $api->category = $apiData['category']['name'] ?? null;
            $api->rapidapi_version_id = $apiData['currentVersion']['id'] ?? null;
            $api->raw_data = $apiData;
            $api->synced_at = now();
            $api->save();

            // Get endpoints
            if ($api->rapidapi_version_id) {
                $endpointsResult = $client->getEndpoints($api->rapidapi_version_id);
                $endpoints = $endpointsResult['apiVersion']['endpoints'] ?? [];

                // Delete old endpoints
                $api->endpoints()->delete();

                // Create new endpoints
                foreach ($endpoints as $endpoint) {
                    $api->endpoints()->create([
                        'name' => $endpoint['name'],
                        'method' => $endpoint['method'],
                        'route' => $endpoint['route'],
                        'description' => $endpoint['description'] ?? null,
                        'parameters' => $endpoint['parameters'] ?? [],
                        'headers' => $endpoint['headers'] ?? [],
                    ]);
                }
            }

            Flash::success('API synced successfully');
            return $this->listRefresh();

        } catch (\Exception $e) {
            Flash::error('Error syncing API: ' . $e->getMessage());
        }
    }

    /**
     * Custom action: Clear cache
     */
    public function onClearCache()
    {
        try {
            \Cache::tags('apihub')->flush();
            \Cache::forget('apihub:stats');

            Flash::success('Cache cleared successfully');
            return $this->listRefresh();
        } catch (\Exception $e) {
            Flash::error('Error clearing cache: ' . $e->getMessage());
        }
    }
}
