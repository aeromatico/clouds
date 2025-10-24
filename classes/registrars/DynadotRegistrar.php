<?php namespace Aero\Clouds\Classes\Registrars;

use Exception;

/**
 * DynadotRegistrar
 * Dynadot API integration for domain pricing sync
 *
 * API Documentation: https://www.dynadot.com/domain/api3.html
 */
class DynadotRegistrar extends AbstractRegistrar
{
    /**
     * Dynadot API endpoint
     */
    const API_ENDPOINT = 'https://api.dynadot.com/api3.json';

    /**
     * Fetch all available TLD extensions from Dynadot
     *
     * @return array Array of extension data
     * @throws Exception
     */
    public function fetchExtensions(): array
    {
        $response = $this->makeApiRequest('tld_price');

        // Log the full response for debugging
        \Log::info('Dynadot TLD Price Response: ' . json_encode($response));

        // Check for generic Response format first (used for errors)
        if (isset($response['Response'])) {
            $responseCode = $response['Response']['ResponseCode'] ?? null;

            if ($responseCode != 0) {
                $error = $response['Response']['Error'] ?? 'Unknown error';
                throw new Exception("Dynadot API Error: {$error}");
            }
        }

        // Check for TldPriceResponse format
        if (!isset($response['TldPriceResponse'])) {
            throw new Exception('Invalid response format from Dynadot API. Response: ' . json_encode($response));
        }

        $data = $response['TldPriceResponse'];

        // Check ResponseCode in the specific response
        if (isset($data['ResponseCode']) && $data['ResponseCode'] != 0) {
            $error = $data['Error'] ?? 'Unknown error';
            throw new Exception("Dynadot API Error: {$error}");
        }

        $extensions = [];
        $tldPrices = $data['TldPriceList'] ?? $data['TldPrice'] ?? [];

        \Log::info('Dynadot TLD Count: ' . count($tldPrices));

        foreach ($tldPrices as $tldData) {
            $extensions[] = $this->parseTldData($tldData);
        }

        return $extensions;
    }

    /**
     * Fetch pricing for a specific TLD
     *
     * @param string $tld The TLD to fetch pricing for
     * @return array Pricing data
     * @throws Exception
     */
    public function fetchPricing(string $tld): array
    {
        // Remove leading dot if present
        $tld = ltrim($tld, '.');

        // Get all TLD prices and filter by the requested one
        // Note: Dynadot API doesn't have a command to get a specific TLD price
        $response = $this->makeApiRequest('tld_price');

        // Check for generic Response format first (used for errors)
        if (isset($response['Response'])) {
            $responseCode = $response['Response']['ResponseCode'] ?? null;

            if ($responseCode != 0) {
                $error = $response['Response']['Error'] ?? 'Unknown error';
                throw new Exception("Dynadot API Error: {$error}");
            }
        }

        // Check for TldPriceResponse format
        if (!isset($response['TldPriceResponse'])) {
            throw new Exception('Invalid response format from Dynadot API. Response: ' . json_encode($response));
        }

        $data = $response['TldPriceResponse'];

        // Check ResponseCode in the specific response
        if (isset($data['ResponseCode']) && $data['ResponseCode'] != 0) {
            $error = $data['Error'] ?? 'Unknown error';
            throw new Exception("Dynadot API Error: {$error}");
        }

        $tldPrices = $data['TldPriceList'] ?? $data['TldPrice'] ?? [];

        // Find the specific TLD
        foreach ($tldPrices as $tldData) {
            $currentTld = ltrim($tldData['Tld'] ?? $tldData['tld'] ?? '', '.');

            if (strtolower($currentTld) === strtolower($tld)) {
                return $this->parseTldData($tldData);
            }
        }

        throw new Exception("TLD '{$tld}' not found in Dynadot pricing list");
    }

    /**
     * Test API connection and credentials
     *
     * @return bool True if connection is successful
     */
    public function testConnection(): bool
    {
        try {
            // Use account_info command to test - it's simple and available to all accounts
            $response = $this->makeApiRequest('account_info');

            // Log the raw response for debugging
            \Log::info('Dynadot API Test Response: ' . json_encode($response));

            // Check for generic Response format first (used for errors)
            if (isset($response['Response'])) {
                $responseCode = $response['Response']['ResponseCode'] ?? null;

                if ($responseCode == 0) {
                    return true;
                }

                // If response code is not 0, get error message
                $error = $response['Response']['Error'] ?? 'Unknown error';
                $this->setError($error);
                return false;
            }

            // Check for AccountInfoResponse format
            if (isset($response['AccountInfoResponse'])) {
                $responseCode = $response['AccountInfoResponse']['ResponseCode'] ?? null;

                if ($responseCode == 0) {
                    return true;
                }

                // If response code is not 0, get error message
                $error = $response['AccountInfoResponse']['Error'] ?? 'Unknown error';
                $this->setError($error);
                return false;
            }

            $this->setError('Invalid response format from Dynadot API. Response: ' . json_encode($response));
            return false;
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * Make API request to Dynadot
     *
     * @param string $command API command
     * @param array $params Additional parameters
     * @return array Response data
     * @throws Exception
     */
    protected function makeApiRequest(string $command, array $params = []): array
    {
        $apiKey = $this->provider->api_key;

        if (empty($apiKey)) {
            throw new Exception('Dynadot API key is not configured');
        }

        $requestParams = array_merge([
            'key' => $apiKey,
            'command' => $command,
        ], $params);

        return $this->makeRequest(self::API_ENDPOINT, $requestParams, 'GET');
    }

    /**
     * Parse TLD data from Dynadot response
     *
     * @param array $tldData Raw TLD data from API
     * @return array Parsed extension data
     */
    protected function parseTldData(array $tldData): array
    {
        $tld = $tldData['Tld'] ?? $tldData['tld'] ?? null;

        if (!$tld) {
            \Log::error('TLD data missing from response: ' . json_encode($tldData));
            throw new Exception('TLD data missing from response');
        }

        // Ensure TLD has a dot prefix
        if (substr($tld, 0, 1) !== '.') {
            $tld = '.' . $tld;
        }

        // Parse pricing (Dynadot returns prices in USD)
        // Prices are nested in 'Price' object
        $priceData = $tldData['Price'] ?? [];
        $registrationPrice = $this->parsePrice($priceData['Register'] ?? $tldData['RegisterPrice'] ?? 0);
        $renewalPrice = $this->parsePrice($priceData['Renew'] ?? $tldData['RenewPrice'] ?? $registrationPrice);
        $transferPrice = $this->parsePrice($priceData['Transfer'] ?? $tldData['TransferPrice'] ?? null);
        $restorePrice = $this->parsePrice($priceData['Restore'] ?? $tldData['RestorePrice'] ?? null);

        // Parse other attributes
        $minYears = (int)($tldData['MinRegisterYear'] ?? $tldData['min_register_year'] ?? 1);
        $maxYears = (int)($tldData['MaxRegisterYear'] ?? $tldData['max_register_year'] ?? 10);

        // WHOIS Privacy - Dynadot uses "Privacy" field with "Yes"/"No" values
        $whoisPrivacy = ($tldData['Privacy'] ?? 'No') === 'Yes';
        $whoisPrivacyPrice = $whoisPrivacy ? $this->parsePrice($priceData['Privacy'] ?? $tldData['WhoisPrivacyPrice'] ?? null) : null;

        $result = [
            'tld' => $tld,
            'name' => ucfirst(str_replace('.', '', $tld)),
            'registration_price' => $registrationPrice,
            'renewal_price' => $renewalPrice,
            'transfer_price' => $transferPrice,
            'redemption_price' => $restorePrice,
            'currency' => 'USD', // Dynadot prices are in USD
            'min_years' => $minYears,
            'max_years' => $maxYears,
            'is_available' => true,
            'whois_privacy_available' => $whoisPrivacy,
            'whois_privacy_price' => $whoisPrivacyPrice,
        ];

        \Log::info("Parsed TLD {$tld}: Reg=${registrationPrice}, Renew=${renewalPrice}");

        return $result;
    }

    /**
     * Check domain availability
     *
     * @param string $domain Full domain name (e.g., example.com)
     * @param bool $showPrice Whether to include pricing information
     * @return array ['available' => bool, 'domain' => string, 'price' => float|null]
     * @throws Exception
     */
    public function checkAvailability(string $domain, bool $showPrice = true): array
    {
        $params = [
            'domain0' => $domain,
        ];

        if ($showPrice) {
            $params['show_price'] = '1';
            $params['currency'] = 'USD';
        }

        $response = $this->makeApiRequest('search', $params);

        \Log::info('Dynadot Domain Search Response: ' . json_encode($response));

        // Check for generic Response format first (used for errors)
        if (isset($response['Response'])) {
            $responseCode = $response['Response']['ResponseCode'] ?? null;

            if ($responseCode != 0) {
                $error = $response['Response']['Error'] ?? 'Unknown error';
                throw new Exception("Dynadot API Error: {$error}");
            }
        }

        // Check for SearchResponse format
        if (!isset($response['SearchResponse'])) {
            throw new Exception('Invalid response format from Dynadot API. Response: ' . json_encode($response));
        }

        $data = $response['SearchResponse'];

        // Check ResponseCode in the specific response
        if (isset($data['ResponseCode']) && $data['ResponseCode'] != 0) {
            $error = $data['Error'] ?? 'Unknown error';
            throw new Exception("Dynadot API Error: {$error}");
        }

        // Get search results - it's an array
        $searchResults = $data['SearchResults'] ?? [];

        // Get first result (we only searched for one domain)
        $domainResult = !empty($searchResults) ? $searchResults[0] : null;

        if (!$domainResult) {
            throw new Exception("Domain result not found in response");
        }

        // Parse availability - Dynadot uses "yes"/"no" for Available field
        $available = ($domainResult['Available'] ?? 'no') === 'yes';
        $price = null;

        // Get price if available - Dynadot returns price as string like "Registration Price: 10.88 in USD..."
        if ($available && $showPrice && isset($domainResult['Price'])) {
            $priceString = $domainResult['Price'];

            // Extract numeric price from string like "Registration Price: 10.88 in USD..."
            if (preg_match('/Registration Price:\s*([\d.]+)/', $priceString, $matches)) {
                $price = $this->parsePrice($matches[1]);
            } else {
                // Try to parse as direct number
                $price = $this->parsePrice($priceString);
            }
        }

        return [
            'available' => $available,
            'domain' => $domainResult['DomainName'] ?? $domain,
            'price' => $price,
            'status' => $domainResult['Status'] ?? 'unknown'
        ];
    }

    /**
     * Parse price from API response
     *
     * @param mixed $price Price in USD
     * @return float|null Price in USD
     */
    protected function parsePrice($price): ?float
    {
        if ($price === null || $price === '') {
            return null;
        }

        return round((float)$price, 2);
    }

    /**
     * Set nameservers for a domain
     *
     * @param string $domain Full domain name
     * @param array $nameservers Array of nameserver hostnames (2-13 nameservers)
     * @return array ['success' => bool, 'message' => string]
     */
    public function setNameservers(string $domain, array $nameservers): array
    {
        try {
            // Validate nameservers count (Dynadot requires 2-13)
            if (count($nameservers) < 2) {
                return [
                    'success' => false,
                    'message' => 'At least 2 nameservers are required'
                ];
            }

            if (count($nameservers) > 13) {
                return [
                    'success' => false,
                    'message' => 'Maximum 13 nameservers allowed'
                ];
            }

            $params = ['domain' => $domain];

            // Add nameservers to params (ns0, ns1, ns2, etc.)
            foreach ($nameservers as $index => $ns) {
                $params['ns' . $index] = trim($ns);
            }

            $response = $this->makeApiRequest('set_ns', $params);

            \Log::info('Dynadot Set Nameservers Response: ' . json_encode($response));

            // Check for SetNsResponse format
            if (isset($response['SetNsResponse'])) {
                $responseCode = $response['SetNsResponse']['ResponseCode'] ?? null;

                if ($responseCode == 0) {
                    return [
                        'success' => true,
                        'message' => 'Nameservers updated successfully'
                    ];
                }

                $error = $response['SetNsResponse']['Error'] ?? 'Unknown error';
                return [
                    'success' => false,
                    'message' => "Failed to update nameservers: {$error}"
                ];
            }

            return [
                'success' => false,
                'message' => 'Invalid response from API'
            ];
        } catch (Exception $e) {
            \Log::error('Error setting nameservers: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get nameservers for a domain
     *
     * @param string $domain Full domain name
     * @return array ['success' => bool, 'nameservers' => array, 'message' => string]
     */
    public function getNameservers(string $domain): array
    {
        try {
            $params = ['domain' => $domain];
            $response = $this->makeApiRequest('get_ns', $params);

            \Log::info('Dynadot Get Nameservers Response: ' . json_encode($response));

            // Check for GetNsResponse format
            if (isset($response['GetNsResponse'])) {
                $data = $response['GetNsResponse'];
                $responseCode = $data['ResponseCode'] ?? null;

                if ($responseCode == 0) {
                    // Extract nameservers from response
                    $nameservers = [];
                    $nsData = $data['NameServers'] ?? [];

                    // Dynadot returns nameservers as ServerName array
                    if (isset($nsData['ServerName'])) {
                        $serverNames = $nsData['ServerName'];

                        // Can be array of strings or single string
                        if (is_array($serverNames)) {
                            $nameservers = $serverNames;
                        } else {
                            $nameservers = [$serverNames];
                        }
                    }

                    return [
                        'success' => true,
                        'nameservers' => $nameservers,
                        'message' => 'Nameservers retrieved successfully'
                    ];
                }

                $error = $data['Error'] ?? 'Unknown error';
                return [
                    'success' => false,
                    'nameservers' => [],
                    'message' => "Failed to get nameservers: {$error}"
                ];
            }

            return [
                'success' => false,
                'nameservers' => [],
                'message' => 'Invalid response from API'
            ];
        } catch (Exception $e) {
            \Log::error('Error getting nameservers: ' . $e->getMessage());
            return [
                'success' => false,
                'nameservers' => [],
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Set DNS records for a domain
     *
     * @param string $domain Full domain name
     * @param array $records Array of DNS records
     * @return array ['success' => bool, 'message' => string]
     */
    public function setDnsRecords(string $domain, array $records): array
    {
        try {
            $params = [
                'domain' => $domain,
                'main_record_type' => 'a' // Default to A records
            ];

            // Parse records and add to params
            $aRecords = [];
            $mxRecords = [];
            $txtRecords = [];
            $cnameRecords = [];

            foreach ($records as $index => $record) {
                $type = strtolower($record['type'] ?? 'a');
                $subdomain = $record['subdomain'] ?? '';
                $value = $record['value'] ?? '';

                switch ($type) {
                    case 'a':
                        $aRecords[] = [
                            'subdomain' => $subdomain,
                            'ip' => $value
                        ];
                        break;
                    case 'mx':
                        $mxRecords[] = [
                            'subdomain' => $subdomain,
                            'mail_server' => $value,
                            'priority' => $record['priority'] ?? 10
                        ];
                        break;
                    case 'txt':
                        $txtRecords[] = [
                            'subdomain' => $subdomain,
                            'text' => $value
                        ];
                        break;
                    case 'cname':
                        $cnameRecords[] = [
                            'subdomain' => $subdomain,
                            'host' => $value
                        ];
                        break;
                }
            }

            // Add A records
            foreach ($aRecords as $i => $record) {
                $params["subdomain{$i}"] = $record['subdomain'];
                $params["ip{$i}"] = $record['ip'];
            }

            $response = $this->makeApiRequest('set_dns', $params);

            \Log::info('Dynadot Set DNS Response: ' . json_encode($response));

            // Check for SetDnsResponse format
            if (isset($response['SetDnsResponse'])) {
                $responseCode = $response['SetDnsResponse']['ResponseCode'] ?? null;

                if ($responseCode == 0) {
                    return [
                        'success' => true,
                        'message' => 'DNS records updated successfully'
                    ];
                }

                $error = $response['SetDnsResponse']['Error'] ?? 'Unknown error';
                return [
                    'success' => false,
                    'message' => "Failed to update DNS records: {$error}"
                ];
            }

            return [
                'success' => false,
                'message' => 'Invalid response from API'
            ];
        } catch (Exception $e) {
            \Log::error('Error setting DNS records: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get DNS records for a domain
     *
     * @param string $domain Full domain name
     * @return array ['success' => bool, 'records' => array, 'message' => string]
     */
    public function getDnsRecords(string $domain): array
    {
        try {
            $params = ['domain' => $domain];
            $response = $this->makeApiRequest('get_dns', $params);

            \Log::info('Dynadot Get DNS Response: ' . json_encode($response));

            // Check for GetDnsResponse format
            if (isset($response['GetDnsResponse'])) {
                $data = $response['GetDnsResponse'];
                $responseCode = $data['ResponseCode'] ?? null;

                if ($responseCode == 0) {
                    // Extract DNS records from response
                    $records = [];
                    $dnsData = $data['DnsSettings'] ?? [];

                    // Parse A records
                    if (isset($dnsData['ARecords'])) {
                        $aRecords = $dnsData['ARecords'];
                        if (!isset($aRecords[0])) {
                            $aRecords = [$aRecords];
                        }

                        foreach ($aRecords as $record) {
                            $records[] = [
                                'type' => 'A',
                                'subdomain' => $record['Subdomain'] ?? '',
                                'value' => $record['Ip'] ?? ''
                            ];
                        }
                    }

                    // Parse MX records
                    if (isset($dnsData['MxRecords'])) {
                        $mxRecords = $dnsData['MxRecords'];
                        if (!isset($mxRecords[0])) {
                            $mxRecords = [$mxRecords];
                        }

                        foreach ($mxRecords as $record) {
                            $records[] = [
                                'type' => 'MX',
                                'subdomain' => $record['Subdomain'] ?? '',
                                'value' => $record['MailServer'] ?? '',
                                'priority' => $record['Priority'] ?? 10
                            ];
                        }
                    }

                    return [
                        'success' => true,
                        'records' => $records,
                        'message' => 'DNS records retrieved successfully'
                    ];
                }

                $error = $data['Error'] ?? 'Unknown error';
                return [
                    'success' => false,
                    'records' => [],
                    'message' => "Failed to get DNS records: {$error}"
                ];
            }

            return [
                'success' => false,
                'records' => [],
                'message' => 'Invalid response from API'
            ];
        } catch (Exception $e) {
            \Log::error('Error getting DNS records: ' . $e->getMessage());
            return [
                'success' => false,
                'records' => [],
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Set domain lock status
     *
     * @param string $domain Full domain name
     * @param bool $lock True to lock, false to unlock
     * @return array ['success' => bool, 'message' => string]
     */
    public function setLock(string $domain, bool $lock): array
    {
        try {
            $params = [
                'domain' => $domain,
                'lock' => $lock ? 'yes' : 'no'
            ];

            $response = $this->makeApiRequest('set_lock', $params);

            \Log::info('Dynadot Set Lock Response: ' . json_encode($response));

            // Check for SetLockResponse format
            if (isset($response['SetLockResponse'])) {
                $responseCode = $response['SetLockResponse']['ResponseCode'] ?? null;

                if ($responseCode == 0) {
                    $action = $lock ? 'locked' : 'unlocked';
                    return [
                        'success' => true,
                        'message' => "Domain {$action} successfully"
                    ];
                }

                $error = $response['SetLockResponse']['Error'] ?? 'Unknown error';
                return [
                    'success' => false,
                    'message' => "Failed to change lock status: {$error}"
                ];
            }

            return [
                'success' => false,
                'message' => 'Invalid response from API'
            ];
        } catch (Exception $e) {
            \Log::error('Error setting domain lock: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get domain EPP/Auth code
     *
     * @param string $domain Full domain name
     * @return array ['success' => bool, 'auth_code' => string|null, 'message' => string]
     */
    public function getAuthCode(string $domain): array
    {
        try {
            $params = ['domain' => $domain];
            $response = $this->makeApiRequest('get_auth_code', $params);

            \Log::info('Dynadot Get Auth Code Response: ' . json_encode($response));

            // Check for GetAuthCodeResponse format
            if (isset($response['GetAuthCodeResponse'])) {
                $data = $response['GetAuthCodeResponse'];
                $responseCode = $data['ResponseCode'] ?? null;

                if ($responseCode == 0) {
                    $authCode = $data['AuthCode'] ?? null;

                    return [
                        'success' => true,
                        'auth_code' => $authCode,
                        'message' => 'Auth code retrieved successfully'
                    ];
                }

                $error = $data['Error'] ?? 'Unknown error';
                return [
                    'success' => false,
                    'auth_code' => null,
                    'message' => "Failed to get auth code: {$error}"
                ];
            }

            return [
                'success' => false,
                'auth_code' => null,
                'message' => 'Invalid response from API'
            ];
        } catch (Exception $e) {
            \Log::error('Error getting auth code: ' . $e->getMessage());
            return [
                'success' => false,
                'auth_code' => null,
                'message' => $e->getMessage()
            ];
        }
    }
}
