<?php namespace Aero\Clouds\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Orders extends Controller
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
        BackendMenu::setContext('Aero.Clouds', 'commerce', 'orders');
    }

    /**
     * AJAX handler: Check domain availability
     */
    public function onCheckDomainAvailability()
    {
        try {
            $domainIndex = post('domainIndex');
            $formData = post('Order');

            \Log::info('Domain Check - Index: ' . $domainIndex);
            \Log::info('Domain Check - Form Data: ' . json_encode($formData));

            // If domainIndex is undefined or null, try to find domain by _index field
            if ($domainIndex === null || $domainIndex === 'undefined' || $domainIndex === '') {
                if (isset($formData['domains']) && is_array($formData['domains'])) {
                    // Try to find the domain with _index field or use first one
                    foreach ($formData['domains'] as $idx => $domain) {
                        if (isset($domain['_index'])) {
                            $domainIndex = $domain['_index'];
                            \Log::info('Using _index field: ' . $domainIndex);
                            break;
                        }
                    }

                    // If still not found, use first domain (index 0)
                    if ($domainIndex === null || $domainIndex === 'undefined' || $domainIndex === '') {
                        $domainIndex = 0;
                        \Log::info('Defaulting to index 0');
                    }
                }
            }

            // Get the domain data from the repeater
            if (!isset($formData['domains'][$domainIndex])) {
                \Log::error('Domain data not found at index: ' . $domainIndex);
                throw new \Exception('Domain data not found at index ' . $domainIndex);
            }

            $domainData = $formData['domains'][$domainIndex];
            $domainName = $domainData['domain_name'] ?? null;
            $extensionId = $domainData['extension_id'] ?? null;

            if (!$domainName) {
                throw new \Exception('Please enter a domain name');
            }

            if (!$extensionId) {
                throw new \Exception('Please select an extension');
            }

            // Get the extension (to get sale_price)
            $extension = \Aero\Clouds\Models\DomainExtension::find($extensionId);
            if (!$extension) {
                throw new \Exception('Extension not found');
            }

            // Build full domain name
            $fullDomain = $domainName . $extension->tld;

            // Always use Dynadot provider for availability checks
            $dynadotProvider = \Aero\Clouds\Models\DomainProvider::where('provider_type', 'dynadot')
                ->where('is_active', true)
                ->first();

            if (!$dynadotProvider) {
                throw new \Exception('Dynadot provider not configured or inactive');
            }

            // Get Dynadot registrar
            $registrar = new \Aero\Clouds\Classes\Registrars\DynadotRegistrar($dynadotProvider);

            // Check availability
            $result = $registrar->checkAvailability($fullDomain);

            // ALWAYS add sale price from the selected extension (for debugging)
            $salePrice = $extension->sale_price ?? $extension->registration_price;
            $result['price'] = $salePrice;

            \Log::info('=== Domain Check Complete ===');
            \Log::info('Domain: ' . $fullDomain);
            \Log::info('Available: ' . ($result['available'] ? 'YES' : 'NO'));
            \Log::info('Sale Price from DB: ' . $salePrice);
            \Log::info('Extension ID: ' . $extension->id . ' - TLD: ' . $extension->tld);
            \Log::info('Full result: ' . json_encode($result));

            return [
                'result' => $result
            ];

        } catch (\Exception $e) {
            throw new \ApplicationException($e->getMessage());
        }
    }

    /**
     * Get registrar adapter for provider
     *
     * @param \Aero\Clouds\Models\DomainProvider $provider
     * @return object|null
     */
    protected function getRegistrar($provider): ?object
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
