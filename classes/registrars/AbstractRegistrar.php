<?php namespace Aero\Clouds\Classes\Registrars;

use Aero\Clouds\Models\DomainProvider;
use Aero\Clouds\Models\DomainExtension;
use Exception;
use Log;

/**
 * AbstractRegistrar
 * Base class for domain registrar integrations
 */
abstract class AbstractRegistrar implements RegistrarInterface
{
    /**
     * @var DomainProvider
     */
    protected $provider;

    /**
     * @var string|null Last error message
     */
    protected $lastError = null;

    /**
     * Constructor
     *
     * @param DomainProvider $provider
     */
    public function __construct(DomainProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Get the last error message
     *
     * @return string|null
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Set the last error message
     *
     * @param string $error
     */
    protected function setError(string $error): void
    {
        $this->lastError = $error;
        Log::error("Registrar Error ({$this->provider->name}): {$error}");
    }

    /**
     * Sync pricing data to database
     *
     * @return array ['success' => bool, 'synced' => int, 'errors' => array]
     */
    public function syncPricing(): array
    {
        $synced = 0;
        $errors = [];

        try {
            $extensions = $this->fetchExtensions();

            foreach ($extensions as $extensionData) {
                try {
                    $this->syncExtension($extensionData);
                    $synced++;
                } catch (Exception $e) {
                    $errors[] = "Error syncing {$extensionData['tld']}: " . $e->getMessage();
                    Log::error($e);
                }
            }

            return [
                'success' => true,
                'synced' => $synced,
                'errors' => $errors
            ];
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return [
                'success' => false,
                'synced' => $synced,
                'errors' => array_merge($errors, [$e->getMessage()])
            ];
        }
    }

    /**
     * Sync a single extension to database
     *
     * @param array $data Extension data
     * @return DomainExtension
     */
    protected function syncExtension(array $data): DomainExtension
    {
        // Ensure TLD format
        $tld = $data['tld'];
        if (substr($tld, 0, 1) !== '.') {
            $tld = '.' . $tld;
        }
        $tld = strtolower($tld);

        // Find or create extension
        $extension = DomainExtension::firstOrNew([
            'provider_id' => $this->provider->id,
            'tld' => $tld
        ]);

        // Update fields
        $extension->name = $data['name'] ?? ucfirst(str_replace('.', '', $tld));
        $extension->category = $data['category'] ?? $this->guessTldCategory($tld);
        $extension->registration_price = $data['registration_price'] ?? 0;
        $extension->renewal_price = $data['renewal_price'] ?? $data['registration_price'] ?? 0;
        $extension->transfer_price = $data['transfer_price'] ?? null;
        $extension->redemption_price = $data['redemption_price'] ?? null;
        $extension->currency = $data['currency'] ?? 'USD';
        $extension->min_years = $data['min_years'] ?? 1;
        $extension->max_years = $data['max_years'] ?? 10;
        $extension->is_available = $data['is_available'] ?? true;
        $extension->whois_privacy_available = $data['whois_privacy_available'] ?? false;
        $extension->whois_privacy_price = $data['whois_privacy_price'] ?? null;

        $extension->save();

        return $extension;
    }

    /**
     * Guess TLD category based on the extension
     *
     * @param string $tld
     * @return string
     */
    protected function guessTldCategory(string $tld): string
    {
        // Remove the dot
        $ext = ltrim($tld, '.');

        // Country codes (2 letters)
        if (strlen($ext) === 2) {
            return 'country';
        }

        // Common generic TLDs
        $generic = ['com', 'net', 'org', 'info', 'biz'];
        if (in_array($ext, $generic)) {
            return 'generic';
        }

        // Sponsored TLDs
        $sponsored = ['gov', 'edu', 'mil', 'int'];
        if (in_array($ext, $sponsored)) {
            return 'sponsored';
        }

        // New gTLDs (common ones)
        $newGtld = ['app', 'dev', 'io', 'ai', 'cloud', 'tech', 'online', 'store', 'shop'];
        if (in_array($ext, $newGtld)) {
            return 'new';
        }

        // Default to new gTLD
        return 'new';
    }

    /**
     * Make HTTP request
     *
     * @param string $url
     * @param array $params
     * @param string $method
     * @return array
     * @throws Exception
     */
    protected function makeRequest(string $url, array $params = [], string $method = 'GET'): array
    {
        $ch = curl_init();

        if ($method === 'GET' && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => !$this->provider->sandbox_mode,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new Exception("cURL Error: {$error}");
        }

        if ($httpCode !== 200) {
            throw new Exception("HTTP Error {$httpCode}: {$response}");
        }

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON Decode Error: " . json_last_error_msg());
        }

        return $decoded;
    }
}
