<?php namespace Aero\Manager\Classes;

use Log;
use Cache;
use Config;
use Exception;
use Carbon\Carbon;

/**
 * Conector robusto y escalable para integración October CMS → WHMCS
 * 
 * Características:
 * - Manejo de errores robusto
 * - Cache inteligente
 * - Retry automático
 * - Rate limiting
 * - Logging detallado
 * - Validación de datos
 * - Configuración flexible
 */
class WhmcsConnector 
{
    // Configuración
    private $config;
    private $retryAttempts = 3;
    private $retryDelay = 1; // segundos
    private $timeout = 30;
    private $cacheEnabled = true;
    private $cacheTtl = 300; // 5 minutos
    
    // Rate limiting
    private $maxRequestsPerMinute = 60;
    private $rateLimitKey = 'whmcs_api_requests';
    
    public function __construct()
    {
        $this->config = [
            'url' => config('aero.manager.whmcs.url', 'https://my.clouds.com.bo/api_october.php'),
            'identifier' => config('aero.manager.whmcs.identifier', ''),
            'secret' => config('aero.manager.whmcs.secret', ''),
            'api_token' => config('aero.manager.whmcs.api_token', '5096eff26bd565cab693db213b9fce88b8e3d124cbcc3c85f20c4a2d7022f38d'),
            'debug' => config('aero.manager.whmcs.debug', false)
        ];
    }
    
    // ========== MÉTODOS PRINCIPALES PARA PRICING ==========
    
    /**
     * Test de conexión con WHMCS
     */
/**
 * Test de conexión con WHMCS
 * Cambia este método en tu WhmcsConnector.php
 */
public function testConnection()
{
    try {
        // Usar GetClients en lugar de GetClientsProducts
        $postfields = [
            'action' => 'GetClients',
            'limitnum' => 1
        ];

        $result = $this->makeApiCall($postfields);
        
        if (!$result['success']) {
            return [
                'success' => false,
                'error' => $result['error']
            ];
        }

        $this->logSuccess('Test connection successful');

        return [
            'success' => true,
            'message' => 'Conexión exitosa con WHMCS',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => 'API Local WHMCS',
            'clients_found' => isset($result['data']['clients']) ? count($result['data']['clients']) : 0
        ];

    } catch (Exception $e) {
        $this->logError('Error en test de conexión', ['error' => $e->getMessage()]);

        return [
            'success' => false,
            'error' => 'Error de conexión: ' . $e->getMessage()
        ];
    }
}

    /**
     * Buscar cliente por email (usando GetClients)
     */
public function searchClient($email)
{
    try {
        $postfields = [
            'action' => 'GetClients',
            'search' => $email,
            'limitnum' => 1
        ];

        $result = $this->makeApiCall($postfields);
        
        if (!$result['success']) {
            return [
                'success' => false,
                'error' => $result['error']
            ];
        }

        // La respuesta de GetClients ahora viene en format success/data
        $responseData = $result['data'];
        
        // Verificar si se encontró el cliente
        if (empty($responseData['clients']['client'])) {
            return [
                'success' => false,
                'error' => 'Cliente no encontrado con email: ' . $email
            ];
        }

        $client = $responseData['clients']['client'][0] ?? $responseData['clients']['client'];
        
        // Verificar que el email coincida exactamente
        if (strtolower($client['email']) !== strtolower($email)) {
            return [
                'success' => false,
                'error' => 'Email no coincide exactamente'
            ];
        }

        $this->logSuccess('Cliente encontrado exitosamente', [
            'client_id' => $client['id'],
            'email' => $email
        ]);

        return [
            'success' => true,
            'data' => $client
        ];

    } catch (Exception $e) {
        $this->logError('Error buscando cliente', [
            'email' => $email,
            'error' => $e->getMessage()
        ]);

        return [
            'success' => false,
            'error' => 'Error de conexión: ' . $e->getMessage()
        ];
    }
}

    /**
     * Crear factura usando CreateInvoice (para pricing)
     */
    public function createInvoice($invoiceData)
    {
        try {
            // Validar datos requeridos
            $required = ['userid', 'itemdescription1', 'itemamount1'];
            foreach ($required as $field) {
                if (!isset($invoiceData[$field]) || empty($invoiceData[$field])) {
                    return [
                        'success' => false,
                        'error' => "Campo requerido faltante: {$field}"
                    ];
                }
            }

            // Preparar datos para WHMCS API
            $postfields = [
                'action' => 'CreateInvoice',
                'userid' => $invoiceData['userid'],
                'status' => $invoiceData['status'] ?? 'Unpaid',
                'sendinvoice' => $invoiceData['sendinvoice'] ?? false ? 1 : 0,
                'paymentmethod' => $invoiceData['paymentmethod'] ?? 'credits',
                'itemdescription[0]' => $invoiceData['itemdescription1'],
                'itemamount[0]' => number_format($invoiceData['itemamount1'], 2, '.', ''),
                'itemtaxed[0]' => 1, // Aplicar impuestos por defecto
                'notes' => $invoiceData['notes'] ?? '',
                'date' => date('Y-m-d'),
                'duedate' => date('Y-m-d', strtotime('+30 days'))
            ];

            $result = $this->makeApiCall($postfields);
            
            if (!$result['success']) {
                return [
                    'success' => false,
                    'error' => $result['error']
                ];
            }

            $this->logSuccess('Factura creada exitosamente', [
                'invoice_id' => $result['data']['invoiceid'],
                'client_id' => $invoiceData['userid'],
                'amount' => $invoiceData['itemamount1']
            ]);

            return [
                'success' => true,
                'data' => $result['data']
            ];

        } catch (Exception $e) {
            $this->logError('Error creando factura', [
                'error' => $e->getMessage(),
                'invoice_data' => $invoiceData
            ]);

            return [
                'success' => false,
                'error' => 'Error de conexión: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener detalles de una factura
     */
    public function getInvoiceDetails($invoiceId)
    {
        try {
            $postfields = [
                'action' => 'GetInvoice',
                'invoiceid' => $invoiceId
            ];

            $result = $this->makeApiCall($postfields);
            
            if (!$result['success']) {
                return [
                    'success' => false,
                    'error' => $result['error']
                ];
            }

            return [
                'success' => true,
                'data' => $result['data']
            ];

        } catch (Exception $e) {
            $this->logError('Error obteniendo detalles de factura', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Error de conexión: ' . $e->getMessage()
            ];
        }
    }
    
/**
 * Crear factura con múltiples items (para carrito)
 * Reemplaza este método en tu WhmcsConnector.php
 */
public function createInvoiceMultipleItems($invoiceData)
{
    try {
        // Validar datos requeridos
        $required = ['userid', 'items'];
        foreach ($required as $field) {
            if (!isset($invoiceData[$field]) || empty($invoiceData[$field])) {
                return [
                    'success' => false,
                    'error' => "Campo requerido faltante: {$field}"
                ];
            }
        }

        // Preparar datos básicos para WHMCS API
        $postfields = [
            'action' => 'CreateInvoice',
            'userid' => $invoiceData['userid'],
            'status' => $invoiceData['status'] ?? 'Unpaid',
            'sendinvoice' => $invoiceData['sendinvoice'] ?? false ? 1 : 0,
            'paymentmethod' => $invoiceData['paymentmethod'] ?? 'credits',
            'notes' => $invoiceData['notes'] ?? '',
            'date' => date('Y-m-d'),
            'duedate' => date('Y-m-d', strtotime('+30 days')),
            'items' => [] // NUEVO: Enviar como array de items
        ];

        // Procesar cada item del carrito
        foreach ($invoiceData['items'] as $index => $item) {
            // Validar estructura del item
            if (!isset($item['description']) || !isset($item['amount'])) {
                return [
                    'success' => false,
                    'error' => "Item {$index}: 'description' y 'amount' son requeridos"
                ];
            }

            // Agregar item al array de items
            $postfields['items'][] = [
                'description' => $item['description'],
                'amount' => number_format($item['amount'], 2, '.', ''),
                'taxed' => $item['taxed'] ?? 1
            ];
        }

        // Log de debug
        $this->logSuccess('Enviando factura con items', [
            'userid' => $invoiceData['userid'],
            'items_count' => count($postfields['items']),
            'items' => $postfields['items'],
            'total_amount' => array_sum(array_column($postfields['items'], 'amount'))
        ]);

        $result = $this->makeApiCall($postfields);
        
        if ($result['success']) {
            $this->logSuccess('Factura con múltiples items creada exitosamente', [
                'invoice_id' => $result['data']['invoiceid'] ?? 'unknown',
                'client_id' => $invoiceData['userid'],
                'items_count' => count($invoiceData['items'])
            ]);
        } else {
            $this->logError('Error en createInvoiceMultipleItems', [
                'error' => $result['error'],
                'postfields' => $postfields
            ]);
        }

        return $result;

    } catch (Exception $e) {
        $this->logError('Exception en createInvoiceMultipleItems', [
            'error' => $e->getMessage(),
            'invoice_data' => $invoiceData,
            'trace' => $e->getTraceAsString()
        ]);

        return [
            'success' => false,
            'error' => 'Error de conexión: ' . $e->getMessage()
        ];
    }
}   

    /**
     * Obtener estadísticas del sistema para dashboard
     */
    public function getSystemStats()
    {
        try {
            // Obtener estadísticas básicas
            $postfields = [
                'action' => 'GetStats'
            ];

            $result = $this->makeApiCall($postfields);
            
            if ($result['success']) {
                return $result['data'];
            }

            // Si GetStats no está disponible, devolver estadísticas básicas
            return [
                'total_clients' => 'N/A',
                'total_invoices' => 'N/A',
                'total_tickets' => 'N/A',
                'system_status' => 'Connected'
            ];

        } catch (Exception $e) {
            $this->logError('Error obteniendo estadísticas', ['error' => $e->getMessage()]);

            return [
                'system_status' => 'Error',
                'error_message' => $e->getMessage()
            ];
        }
    }
    
    // ========== GESTIÓN DE CLIENTES ==========
    
    /**
     * Crear cliente en WHMCS
     */
    public function createClient(array $data): array
    {
        $this->validateClientData($data);
        
        $postfields = [
            'action' => 'AddClient',
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'address1' => $data['address1'] ?? '',
            'address2' => $data['address2'] ?? '',
            'city' => $data['city'] ?? '',
            'state' => $data['state'] ?? '',
            'postcode' => $data['postcode'] ?? '',
            'country' => $data['country'] ?? 'BO',
            'phonenumber' => $data['phone'] ?? '',
            'password2' => $data['password'] ?? $this->generatePassword(),
            'currency' => $data['currency'] ?? 1,
            'groupid' => $data['group_id'] ?? 0,
            'language' => $data['language'] ?? 'spanish',
            'clientip' => request()->ip(),
            'notes' => $data['notes'] ?? '',
            'noemail' => $data['send_welcome_email'] ?? true ? 0 : 1
        ];
        
        $result = $this->makeApiCall($postfields);
        
        if ($result['success'] && isset($result['data']['clientid'])) {
            $this->logSuccess('Client created', [
                'whmcs_id' => $result['data']['clientid'],
                'email' => $data['email']
            ]);
        }
        
        return $result;
    }
    
    /**
     * Actualizar cliente en WHMCS
     */
    public function updateClient(int $clientId, array $data): array
    {
        $postfields = array_merge([
            'action' => 'UpdateClient',
            'clientid' => $clientId
        ], $this->filterUpdateableClientFields($data));
        
        $result = $this->makeApiCall($postfields);
        
        if ($result['success']) {
            $this->clearClientCache($clientId);
            $this->logSuccess('Client updated', ['whmcs_id' => $clientId]);
        }
        
        return $result;
    }
    
    /**
     * Obtener información de cliente (con cache)
     */
    public function getClient(int $clientId, bool $useCache = true): array
    {
        $cacheKey = "whmcs_client_{$clientId}";
        
        if ($useCache && $this->cacheEnabled) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }
        
        $postfields = [
            'action' => 'GetClientsDetails',
            'clientid' => $clientId,
            'stats' => true
        ];
        
        $result = $this->makeApiCall($postfields);
        
        if ($result['success'] && $useCache && $this->cacheEnabled) {
            Cache::put($cacheKey, $result, $this->cacheTtl);
        }
        
        return $result;
    }
    
    // ========== GESTIÓN DE FACTURAS/RECIBOS ==========
    
    /**
     * Marcar factura como pagada
     */
    public function markInvoicePaid(int $invoiceId, array $paymentData = []): array
    {
        $postfields = [
            'action' => 'AddInvoicePayment',
            'invoiceid' => $invoiceId,
            'transid' => $paymentData['transaction_id'] ?? 'October-' . uniqid(),
            'gateway' => $paymentData['gateway'] ?? 'October CMS',
            'date' => $paymentData['date'] ?? Carbon::now()->format('Y-m-d H:i:s'),
            'amount' => $paymentData['amount'] ?? '', // Si vacío, paga el total
            'fees' => $paymentData['fees'] ?? 0,
            'noemail' => $paymentData['send_email'] ?? true ? 0 : 1
        ];
        
        $result = $this->makeApiCall($postfields);
        
        if ($result['success']) {
            $this->logSuccess('Invoice payment added', [
                'invoice_id' => $invoiceId,
                'amount' => $paymentData['amount'] ?? 'full',
                'gateway' => $paymentData['gateway'] ?? 'October CMS'
            ]);
        }
        
        return $result;
    }
    
    /**
     * Obtener facturas de un cliente
     */
    public function getClientInvoices(int $clientId, string $status = '', int $limitstart = 0, int $limitnum = 25): array
    {
        $postfields = [
            'action' => 'GetInvoices',
            'userid' => $clientId,
            'status' => $status,
            'limitstart' => $limitstart,
            'limitnum' => $limitnum
        ];
        
        return $this->makeApiCall($postfields);
    }
    
    // ========== GESTIÓN DE TICKETS ==========
    
    /**
     * Crear ticket en WHMCS
     */
    public function createTicket(array $data): array
    {
        $this->validateTicketData($data);
        
        $postfields = [
            'action' => 'OpenTicket',
            'clientid' => $data['client_id'],
            'deptid' => $data['department_id'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'priority' => $data['priority'] ?? 'Medium',
            'serviceid' => $data['service_id'] ?? '',
            'admin' => $data['admin'] ?? false,
            'noemail' => $data['send_email'] ?? true ? 0 : 1
        ];
        
        $result = $this->makeApiCall($postfields);
        
        if ($result['success'] && isset($result['data']['id'])) {
            $this->logSuccess('Ticket created', [
                'ticket_id' => $result['data']['id'],
                'client_id' => $data['client_id'],
                'subject' => $data['subject']
            ]);
        }
        
        return $result;
    }
    
    /**
     * Responder a un ticket
     */
    public function replyTicket(int $ticketId, string $message, array $options = []): array
    {
        $postfields = [
            'action' => 'AddTicketReply',
            'ticketid' => $ticketId,
            'message' => $message,
            'clientid' => $options['client_id'] ?? '',
            'contactid' => $options['contact_id'] ?? '',
            'admin' => $options['admin'] ?? false,
            'noemail' => $options['send_email'] ?? true ? 0 : 1
        ];
        
        return $this->makeApiCall($postfields);
    }
    
    // ========== GESTIÓN DE PRECIOS/PRODUCTOS ==========
    
    /**
     * Sincronizar precios desde October CMS a WHMCS
     */
    public function syncPricing(array $pricingData): array
    {
        $results = [];
        
        foreach ($pricingData as $productData) {
            $result = $this->updateProductPricing(
                $productData['product_id'],
                $productData['pricing']
            );
            
            $results[] = [
                'product_id' => $productData['product_id'],
                'result' => $result
            ];
        }
        
        $this->logSuccess('Pricing sync completed', [
            'products_updated' => count($pricingData),
            'successful' => count(array_filter($results, fn($r) => $r['result']['success']))
        ]);
        
        return $results;
    }
    
    /**
     * Actualizar precios de un producto
     */
    public function updateProductPricing(int $productId, array $pricing): array
    {
        $postfields = [
            'action' => 'UpdateProduct',
            'pid' => $productId
        ];
        
        // Agregar precios por período
        $billingCycles = ['monthly', 'quarterly', 'semiannually', 'annually', 'biennially', 'triennially'];
        
        foreach ($billingCycles as $cycle) {
            if (isset($pricing[$cycle])) {
                $postfields[$cycle] = number_format($pricing[$cycle], 2, '.', '');
            }
        }
        
        // Precio de setup
        if (isset($pricing['setup_fee'])) {
            $postfields['setupfee'] = number_format($pricing['setup_fee'], 2, '.', '');
        }
        
        return $this->makeApiCall($postfields);
    }
    
    /**
     * Obtener productos y precios
     */
    public function getProducts(int $groupId = null): array
    {
        $postfields = [
            'action' => 'GetProducts'
        ];
        
        if ($groupId) {
            $postfields['gid'] = $groupId;
        }
        
        return $this->makeApiCall($postfields);
    }
    
    // ========== MÉTODOS AUXILIARES ==========
    
    /**
     * Realizar llamada a la API con retry y rate limiting
     */
    public function makeApiCall(array $postfields): array
    {
        // Rate limiting
        if (!$this->checkRateLimit()) {
            return [
                'success' => false,
                'error' => 'Rate limit exceeded. Try again later.',
                'error_code' => 'RATE_LIMIT_EXCEEDED'
            ];
        }
        
        $lastError = null;
        
        // Retry automático
        for ($attempt = 1; $attempt <= $this->retryAttempts; $attempt++) {
            try {
                $result = $this->executeCurlRequest($postfields);
                
                if ($result['success']) {
                    $this->recordApiRequest();
                    return $result;
                }
                
                $lastError = $result['error'];
                
                // Si es error de autenticación, no reintentamos
                if (isset($result['error_code']) && in_array($result['error_code'], ['AUTH_ERROR', 'INVALID_CREDENTIALS'])) {
                    break;
                }
                
            } catch (Exception $e) {
                $lastError = $e->getMessage();
            }
            
            // Esperar antes del siguiente intento
            if ($attempt < $this->retryAttempts) {
                sleep($this->retryDelay * $attempt);
            }
        }
        
        $this->logError('API call failed after retries', [
            'action' => $postfields['action'],
            'attempts' => $this->retryAttempts,
            'last_error' => $lastError
        ]);
        
        return [
            'success' => false,
            'error' => $lastError,
            'attempts' => $this->retryAttempts
        ];
    }
    
    /**
     * Ejecutar request cURL
     */
    private function executeCurlRequest(array $postfields): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->config['url'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($postfields),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Aero-Manager-October-CMS/2.0',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->config['api_token'],
                'Cache-Control: no-cache'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new Exception("cURL error: " . $curlError);
        }
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP error: " . $httpCode . " - Response: " . $response);
        }
        
        $result = json_decode($response, true);
        
        if (!$result) {
            throw new Exception("Invalid JSON response: " . $response);
        }
        
        // Para el bridge api_october.php, verificar estructura de respuesta
        if (isset($result['success'])) {
            return [
                'success' => $result['success'],
                'data' => $result['data'] ?? [],
                'error' => $result['error'] ?? null,
                'http_code' => $httpCode,
                'raw_response' => $response
            ];
        }
        
        // Para respuestas directas de WHMCS
        if (isset($result['result']) && $result['result'] === 'error') {
            return [
                'success' => false,
                'error' => $result['message'] ?? 'Unknown WHMCS API error',
                'error_code' => $this->getErrorCode($result['message'] ?? ''),
                'raw_response' => $response
            ];
        }
        
        return [
            'success' => true,
            'http_code' => $httpCode,
            'data' => $result,
            'raw_response' => $response
        ];
    }
    
    // ========== MÉTODOS AUXILIARES PRIVADOS ==========
    
    private function validateClientData(array $data): void
    {
        $required = ['firstname', 'lastname', 'email'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field '{$field}' is required for client creation");
            }
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address");
        }
    }
    
    private function validateTicketData(array $data): void
    {
        $required = ['client_id', 'department_id', 'subject', 'message'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field '{$field}' is required for ticket creation");
            }
        }
    }
    
    private function generatePassword(int $length = 12): string
    {
        return str_random($length);
    }
    
    private function filterUpdateableClientFields(array $data): array
    {
        $allowed = [
            'firstname', 'lastname', 'companyname', 'email', 'address1', 'address2',
            'city', 'state', 'postcode', 'country', 'phonenumber', 'password2',
            'currency', 'groupid', 'language', 'notes'
        ];
        
        return array_intersect_key($data, array_flip($allowed));
    }
    
    private function clearClientCache(int $clientId): void
    {
        if ($this->cacheEnabled) {
            Cache::forget("whmcs_client_{$clientId}");
        }
    }
    
    private function checkRateLimit(): bool
    {
        $requests = Cache::get($this->rateLimitKey, []);
        $now = time();
        
        // Limpiar requests antiguos (más de 1 minuto)
        $requests = array_filter($requests, fn($timestamp) => $now - $timestamp < 60);
        
        if (count($requests) >= $this->maxRequestsPerMinute) {
            return false;
        }
        
        return true;
    }
    
    private function recordApiRequest(): void
    {
        $requests = Cache::get($this->rateLimitKey, []);
        $requests[] = time();
        Cache::put($this->rateLimitKey, $requests, 120); // 2 minutos
    }
    
    private function getErrorCode(string $message): string
    {
        if (strpos($message, 'Invalid Credentials') !== false) {
            return 'INVALID_CREDENTIALS';
        }
        if (strpos($message, 'Authentication Failed') !== false) {
            return 'AUTH_ERROR';
        }
        if (strpos($message, 'Client Not Found') !== false) {
            return 'CLIENT_NOT_FOUND';
        }
        
        return 'UNKNOWN_ERROR';
    }
    
    private function logSuccess(string $message, array $context = []): void
    {
        Log::info("WHMCS Success: {$message}", $context);
    }
    
    private function logError(string $message, array $context = []): void
    {
        Log::error("WHMCS Error: {$message}", $context);
    }
}