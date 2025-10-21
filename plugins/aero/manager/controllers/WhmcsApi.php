<?php namespace Aero\Manager\Controllers;

use Backend\Classes\Controller;
use Illuminate\Http\Request;
use Log;

class WhmcsApi extends Controller
{
    private $validToken = '5096eff26bd565cab693db213b9fce88b8e3d124cbcc3c85f20c4a2d7022f38d';
    
    /**
     * Endpoint principal para recibir datos de WHMCS
     */
    public function receive(Request $request)
    {
        try {
            // Validar autenticación
            if (!$this->validateRequest($request)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            
            // Obtener datos
            $data = $request->all();
            $action = $data['action'] ?? 'unknown';
            $payload = $data['data'] ?? [];
            
            // Log de la recepción
            Log::info("WHMCS Data Received", [
                'action' => $action,
                'payload' => $payload,
                'timestamp' => now()
            ]);
            
            // Procesar según la acción
            $result = $this->processAction($action, $payload);
            
            return response()->json([
                'success' => true,
                'action' => $action,
                'message' => 'Data processed successfully',
                'result' => $result,
                'timestamp' => time()
            ]);
            
        } catch (\Exception $e) {
            Log::error("WHMCS API Error: " . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Endpoint de prueba
     */
    public function test(Request $request)
    {
        if (!$this->validateRequest($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'October CMS is ready to receive WHMCS data!',
            'timestamp' => time(),
            'server_info' => [
                'php_version' => PHP_VERSION,
                'october_version' => \System::VERSION
            ]
        ]);
    }
    
    /**
     * Validar token de autenticación
     */
    private function validateRequest(Request $request)
    {
        $authHeader = $request->header('Authorization');
        $expectedToken = 'Bearer ' . $this->validToken;
        
        return $authHeader === $expectedToken;
    }
    
    /**
     * Procesar acciones específicas de WHMCS
     */
    private function processAction($action, $payload)
    {
        switch ($action) {
            case 'client_created':
                return $this->handleClientCreated($payload);
                
            case 'invoice_created':
                return $this->handleInvoiceCreated($payload);
                
            case 'invoice_paid':
                return $this->handleInvoicePaid($payload);
                
            case 'test_connection':
            case 'manual_test':
                return $this->handleTest($payload);
                
            default:
                return ['message' => "Action '$action' processed but no specific handler defined"];
        }
    }
    
    /**
     * Manejar cliente creado
     */
    private function handleClientCreated($data)
    {
        // Aquí puedes:
        // 1. Crear usuario en October CMS
        // 2. Sincronizar datos con tu sistema
        // 3. Enviar emails de bienvenida
        // 4. Etc.
        
        Log::info("New WHMCS Client", $data);
        
        return [
            'action' => 'client_created',
            'client_id' => $data['client_id'] ?? null,
            'email' => $data['email'] ?? null,
            'processed_at' => now()->toDateTimeString()
        ];
    }
    
    /**
     * Manejar factura creada
     */
    private function handleInvoiceCreated($data)
    {
        Log::info("New WHMCS Invoice", $data);
        
        return [
            'action' => 'invoice_created',
            'invoice_id' => $data['invoice_id'] ?? null,
            'amount' => $data['total'] ?? null,
            'processed_at' => now()->toDateTimeString()
        ];
    }
    
    /**
     * Manejar pago de factura
     */
    private function handleInvoicePaid($data)
    {
        Log::info("WHMCS Invoice Paid", $data);
        
        return [
            'action' => 'invoice_paid',
            'invoice_id' => $data['invoice_id'] ?? null,
            'amount' => $data['amount'] ?? null,
            'processed_at' => now()->toDateTimeString()
        ];
    }
    
    /**
     * Manejar pruebas
     */
    private function handleTest($data)
    {
        return [
            'message' => 'Test received successfully!',
            'received_data' => $data,
            'october_response' => 'October CMS is working perfectly with WHMCS'
        ];
    }
}