<?php namespace Aero\Manager\Classes;

use Aero\Manager\Classes\WhmcsConnector;

/**
 * Servicio helper para operaciones WHMCS comunes
 * Simplifica el uso del WhmcsConnector
 */
class WhmcsService
{
    private $connector;
    
    public function __construct()
    {
        $this->connector = new WhmcsConnector();
    }
    
    /**
     * Crear cliente completo con validaciones
     */
    public function createClientFromAeroData(array $aeroClientData): array
    {
        // Mapear datos de Aero Manager a formato WHMCS
        $whmcsData = $this->mapAeroClientToWhmcs($aeroClientData);
        
        // Crear en WHMCS
        $result = $this->connector->createClient($whmcsData);
        
        if ($result['success']) {
            // Guardar relación en tu modelo local
            $this->saveClientMapping($aeroClientData['id'], $result['data']['clientid']);
        }
        
        return $result;
    }
    
    /**
     * Generar factura desde servicios de Aero Manager
     */
    public function generateInvoiceFromServices(int $clientId, array $services): array
    {
        $items = [];
        
        foreach ($services as $service) {
            $items[] = [
                'description' => $service['name'],
                'amount' => $service['price'],
                'taxed' => $service['taxable'] ?? true
            ];
        }
        
        $invoiceData = [
            'client_id' => $clientId,
            'items' => $items,
            'notes' => 'Generado automáticamente desde Aero Manager'
        ];
        
        return $this->connector->createInvoice($invoiceData);
    }
    
    /**
     * Marcar factura como pagada con datos de Aero Manager
     */
    public function markInvoicePaidFromAero(int $invoiceId, array $paymentData): array
    {
        $whmcsPaymentData = [
            'transaction_id' => 'AERO-' . ($paymentData['transaction_id'] ?? uniqid()),
            'gateway' => $paymentData['method'] ?? 'Aero Manager',
            'amount' => $paymentData['amount'],
            'fees' => $paymentData['fees'] ?? 0,
            'date' => $paymentData['date'] ?? now()->format('Y-m-d H:i:s')
        ];
        
        return $this->connector->markInvoicePaid($invoiceId, $whmcsPaymentData);
    }
    
    /**
     * Sincronizar precios masivamente
     */
    public function syncAllPricing(): array
    {
        // Obtener precios desde tu plugin Aero Manager
        $aeroPricing = $this->getAeroPricing();
        
        // Convertir a formato WHMCS
        $whmcsPricing = $this->mapAeroPricingToWhmcs($aeroPricing);
        
        // Sincronizar
        return $this->connector->syncPricing($whmcsPricing);
    }
    
    /**
     * Crear ticket desde Aero Manager
     */
    public function createTicketFromAero(array $ticketData): array
    {
        $whmcsTicketData = [
            'client_id' => $ticketData['client_id'],
            'department_id' => $this->mapDepartment($ticketData['category']),
            'subject' => $ticketData['subject'],
            'message' => $ticketData['message'],
            'priority' => $ticketData['priority'] ?? 'Medium'
        ];
        
return $this->connector->createTicket($whmcsTicketData);
   }
   
   /**
    * Buscar cliente por email
    */
   public function findClientByEmail(string $email): ?array
   {
       // WHMCS no tiene búsqueda directa por email en API
       // Implementar cache local o búsqueda alternativa
       $cacheKey = "whmcs_client_email_{$email}";
       
       return Cache::remember($cacheKey, 300, function() use ($email) {
           // Implementar lógica de búsqueda
           // Por ahora retornamos null, pero puedes implementar
           // búsqueda en tu base de datos local de mapeos
           return null;
       });
   }
   
   // ========== MÉTODOS PRIVADOS DE MAPEO ==========
   
   private function mapAeroClientToWhmcs(array $aeroData): array
   {
       return [
           'firstname' => $aeroData['first_name'],
           'lastname' => $aeroData['last_name'],
           'email' => $aeroData['email'],
           'address1' => $aeroData['address'] ?? '',
           'city' => $aeroData['city'] ?? '',
           'country' => $aeroData['country'] ?? 'BO',
           'phone' => $aeroData['phone'] ?? '',
           'password' => $aeroData['password'] ?? str_random(12),
           'notes' => "Cliente creado desde Aero Manager ID: {$aeroData['id']}"
       ];
   }
   
   private function mapDepartment(string $category): int
   {
       $departments = config('aero.manager.whmcs.ticket_departments', [
           'general' => 1,
           'technical' => 2,
           'billing' => 3,
           'sales' => 4
       ]);
       
       return $departments[$category] ?? $departments['general'];
   }
   
   private function saveClientMapping(int $aeroClientId, int $whmcsClientId): void
   {
       // Implementar guardado en tu modelo
       // Por ejemplo:
       // AeroClient::where('id', $aeroClientId)
       //     ->update(['whmcs_client_id' => $whmcsClientId]);
   }
   
   private function getAeroPricing(): array
   {
       // Implementar obtención de precios desde tu plugin
       // Por ejemplo:
       // return AeroService::all()->map(function($service) {
       //     return [
       //         'aero_service_id' => $service->id,
       //         'whmcs_product_id' => $service->whmcs_product_id,
       //         'pricing' => $service->pricing
       //     ];
       // })->toArray();
       
       return [];
   }
   
   private function mapAeroPricingToWhmcs(array $aeroPricing): array
   {
       return array_map(function($pricing) {
           return [
               'product_id' => $pricing['whmcs_product_id'],
               'pricing' => [
                   'monthly' => $pricing['pricing']['monthly'] ?? 0,
                   'quarterly' => $pricing['pricing']['quarterly'] ?? 0,
                   'semiannually' => $pricing['pricing']['semiannually'] ?? 0,
                   'annually' => $pricing['pricing']['annually'] ?? 0,
                   'setup_fee' => $pricing['pricing']['setup_fee'] ?? 0
               ]
           ];
       }, $aeroPricing);
   }
}