<?php namespace Aero\Manager\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Aero\Manager\Classes\WhmcsConnector;
use Illuminate\Http\Request;
use Exception;

class WhmcsController extends Controller
{
    public $implement = [];
    public $requiredPermissions = ['aero.manager.access_whmcs'];

    protected $whmcsConnector;

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aero.Manager', 'whmcs');
        $this->whmcsConnector = new WhmcsConnector();
    }

    /**
     * Buscar cliente por email
     * GET /api-whmcs/clients/search?email=usuario@example.com
     */
    public function searchClientByEmail(Request $request)
    {
        try {
            $email = $request->get('email');
            
            if (!$email) {
                return response()->json([
                    'success' => false,
                    'error' => 'Email es requerido'
                ], 400);
            }

            // Buscar cliente en WHMCS por email
            $result = $this->whmcsConnector->searchClient($email);
            
            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error']
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $result['data']
            ]);

        } catch (Exception $e) {
            \Log::error('Error buscando cliente: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Crear factura desde el pricing
     * POST /api-whmcs/invoices/create-from-pricing
     */
    public function createInvoiceFromPricing(Request $request)
    {
        try {
            $data = $request->all();
            
            // Validar datos requeridos
            $required = ['email', 'family_name', 'plan_name', 'amount_credits'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return response()->json([
                        'success' => false,
                        'error' => "Campo requerido faltante: {$field}"
                    ], 400);
                }
            }

            // 1. Buscar cliente por email
            $clientResult = $this->whmcsConnector->searchClient($data['email']);
            
            if (!$clientResult['success']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cliente no encontrado: ' . $clientResult['error']
                ], 404);
            }

            $clientId = $clientResult['data']['userid'];

            // 2. Preparar datos de la factura
            $invoiceData = [
                'userid' => $clientId,
                'status' => 'Unpaid',
                'sendinvoice' => false, // No enviar por email automáticamente
                'paymentmethod' => 'credits', // Método de pago por defecto
                'itemdescription1' => $data['family_name'] . ' - ' . $data['plan_name'],
                'itemamount1' => $data['amount_credits'],
                'notes' => 'Factura generada desde el sistema de precios - ' . date('Y-m-d H:i:s')
            ];

            // 3. Crear factura en WHMCS
            $invoiceResult = $this->whmcsConnector->createInvoice($invoiceData);
            
            if (!$invoiceResult['success']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error creando factura: ' . $invoiceResult['error']
                ], 500);
            }

            // 4. Respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Factura creada exitosamente',
                'data' => [
                    'invoice_id' => $invoiceResult['data']['invoiceid'],
                    'client_id' => $clientId,
                    'client_name' => $clientResult['data']['firstname'] . ' ' . $clientResult['data']['lastname'],
                    'amount' => $data['amount_credits'],
                    'description' => $invoiceData['itemdescription1'],
                    'whmcs_invoice_url' => config('whmcs.base_url') . '/admin/invoices.php?action=edit&id=' . $invoiceResult['data']['invoiceid']
                ]
            ]);

        } catch (Exception $e) {
            \Log::error('Error creando factura desde pricing: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }
    
    /**
     * Procesar carrito y generar factura en WHMCS
     * POST /api-whmcs/cart/process
     */
/**
 * Procesar carrito y generar factura en WHMCS
 * POST /api-whmcs/cart/process
 * VERSIÓN FINAL - Reemplaza tu método processCart con este
 */
public function processCart(Request $request)
{
    try {
        $data = $request->all();
        
        // Validar datos requeridos
        if (!isset($data['email']) || !isset($data['items']) || empty($data['items'])) {
            return response()->json([
                'success' => false,
                'error' => 'Email e items son requeridos'
            ], 400);
        }

        $email = $data['email'];
        $items = $data['items'];

        // 1. Buscar cliente por email
        $clientResult = $this->whmcsConnector->searchClient($email);
        
        if (!$clientResult['success']) {
            return response()->json([
                'success' => false,
                'error' => 'Cliente no encontrado: ' . $clientResult['error']
            ], 404);
        }

        $client = $clientResult['data'];
        $clientId = $client['userid'] ?? $client['id'];

        // 2. Procesar items
        $invoiceItems = [];
        $totalAmount = 0;
        
        foreach ($items as $item) {
            $itemPrice = floatval($item['price']);
            $invoiceItems[] = [
                'description' => trim($item['name']),
                'amount' => $itemPrice,
                'taxed' => 1
            ];
            $totalAmount += $itemPrice;
        }

        // 3. Crear factura en WHMCS usando el cliente real
        $invoiceData = [
            'userid' => $clientId,
            'status' => 'Unpaid',
            'sendinvoice' => false,
            'paymentmethod' => 'credits',
            'items' => $invoiceItems,
            'notes' => 'Factura generada desde carrito - ' . date('Y-m-d H:i:s')
        ];

        $invoiceResult = $this->whmcsConnector->createInvoiceMultipleItems($invoiceData);
        
        if (!$invoiceResult['success']) {
            return response()->json([
                'success' => false,
                'error' => 'Error creando factura: ' . $invoiceResult['error']
            ], 500);
        }

        $invoiceId = $invoiceResult['data']['invoiceid'];

        return response()->json([
            'success' => true,
            'message' => 'Carrito procesado exitosamente',
            'data' => [
                'invoice_id' => $invoiceId,
                'client_id' => $clientId,
                'client_name' => $client['firstname'] . ' ' . $client['lastname'],
                'total_amount' => $totalAmount,
                'total_items' => count($items),
                'invoice_url' => 'https://my.clouds.com.bo/viewinvoice.php?id=' . $invoiceId
            ]
        ]);

    } catch (Exception $e) {
        \Log::error('Error procesando carrito: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'error' => 'Error interno del servidor'
        ], 500);
    }
}
    
    /**
     * Test simple del carrito
     */
    public function testCartSimple(Request $request)
    {
        \Log::info('Test cart simple ejecutado');
        
        return response()->json([
            'success' => true,
            'message' => 'Método del carrito funcionando',
            'data' => $request->all(),
            'timestamp' => now()->toISOString()
        ]);
    }
    
    /**
     * Test de conectividad
     * GET /api-whmcs/test
     */
    public function testConnection()
    {
        try {
            $result = $this->whmcsConnector->testConnection();
            
            return response()->json([
                'success' => true,
                'message' => 'Conexión exitosa con WHMCS',
                'data' => $result,
                'timestamp' => now()->toISOString()
            ]);

        } catch (Exception $e) {
            \Log::error('Error en test de conectividad WHMCS: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Error de conexión: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalles de factura
     * GET /api-whmcs/invoices/{id}/details
     */
    public function getInvoiceDetails($invoiceId)
    {
        try {
            $result = $this->whmcsConnector->getInvoiceDetails($invoiceId);
            
            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error']
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $result['data']
            ]);

        } catch (Exception $e) {
            \Log::error('Error obteniendo detalles de factura: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }
    
public function cartSimple(Request $request)
{
    return response()->json([
        'success' => true,
        'message' => 'Cart simple funcionando',
        'data' => $request->all()
    ]);
}    

    /**
     * Dashboard del sistema WHMCS
     * GET /api-whmcs/dashboard
     */
    public function dashboard()
    {
        try {
            $stats = $this->whmcsConnector->getSystemStats();
            
            return response()->json([
                'success' => true,
                'message' => 'Sistema WHMCS operativo',
                'data' => [
                    'timestamp' => now()->toISOString(),
                    'connector_version' => '1.0.0',
                    'stats' => $stats,
                    'system_status' => 'online'
                ]
            ]);

        } catch (Exception $e) {
            \Log::error('Error en dashboard WHMCS: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo estadísticas del sistema'
            ], 500);
        }
    }
}