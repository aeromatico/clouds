<?php namespace Aero\Clouds\Updates;

use Aero\Clouds\Models\PaymentGateway;
use Aero\Clouds\Classes\DomainHelper;
use October\Rain\Database\Updates\Seeder;

class SeedPaymentGateways extends Seeder
{
    public function run()
    {
        // Obtener el dominio actual
        $currentDomain = DomainHelper::current();

        $gateways = [
            [
                'name' => 'QR Simplex',
                'slug' => 'qr-simplex',
                'type' => 'qr_code',
                'description' => 'Pago mediante código QR con QR Simplex',
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 1,
                'configuration' => null,
                'supported_currencies' => ['BOB'],
                'transaction_fee_type' => null,
                'transaction_fee_amount' => 0,
                'transaction_fee_percentage' => 0,
                'min_amount' => 1,
                'max_amount' => null,
                'instructions' => 'Escanee el código QR para realizar el pago y envíe el comprobante.',
                'domain' => $currentDomain
            ],
            [
                'name' => 'Transferencia Bancaria',
                'slug' => 'transferencia-bancaria',
                'type' => 'bank_transfer',
                'description' => 'Transferencia bancaria directa',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 2,
                'configuration' => json_encode([
                    'bank_name' => 'Banco Nacional de Bolivia',
                    'account_number' => '1234567890',
                    'account_holder' => 'Clouds Bolivia',
                    'swift_code' => ''
                ]),
                'supported_currencies' => ['BOB', 'USD'],
                'transaction_fee_type' => null,
                'transaction_fee_amount' => 0,
                'transaction_fee_percentage' => 0,
                'min_amount' => 10,
                'max_amount' => null,
                'instructions' => 'Realice la transferencia a la cuenta indicada y envíe el comprobante.',
                'domain' => $currentDomain
            ],
            [
                'name' => 'Pago Manual',
                'slug' => 'pago-manual',
                'type' => 'manual',
                'description' => 'Pago manual procesado por el administrador',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 3,
                'configuration' => null,
                'supported_currencies' => ['BOB', 'USD'],
                'transaction_fee_type' => null,
                'transaction_fee_amount' => 0,
                'transaction_fee_percentage' => 0,
                'min_amount' => null,
                'max_amount' => null,
                'instructions' => 'Contacte al administrador para coordinar el pago.',
                'domain' => $currentDomain
            ],
            [
                'name' => 'Stripe',
                'slug' => 'stripe',
                'type' => 'stripe',
                'description' => 'Pagos con tarjeta de crédito/débito vía Stripe',
                'is_active' => false,
                'is_default' => false,
                'sort_order' => 4,
                'configuration' => json_encode([
                    'public_key' => '',
                    'secret_key' => '',
                    'webhook_secret' => ''
                ]),
                'supported_currencies' => ['USD', 'EUR'],
                'transaction_fee_type' => 'both',
                'transaction_fee_amount' => 0.30,
                'transaction_fee_percentage' => 2.9,
                'min_amount' => 0.50,
                'max_amount' => null,
                'instructions' => 'Complete el pago con su tarjeta de crédito o débito.',
                'domain' => $currentDomain
            ],
            [
                'name' => 'PayPal',
                'slug' => 'paypal',
                'type' => 'paypal',
                'description' => 'Pagos mediante PayPal',
                'is_active' => false,
                'is_default' => false,
                'sort_order' => 5,
                'configuration' => json_encode([
                    'client_id' => '',
                    'client_secret' => '',
                    'mode' => 'sandbox'
                ]),
                'supported_currencies' => ['USD', 'EUR', 'BOB'],
                'transaction_fee_type' => 'both',
                'transaction_fee_amount' => 0.30,
                'transaction_fee_percentage' => 3.49,
                'min_amount' => 1,
                'max_amount' => null,
                'instructions' => 'Será redirigido a PayPal para completar el pago.',
                'domain' => $currentDomain
            ],
            [
                'name' => 'Criptomonedas',
                'slug' => 'criptomonedas',
                'type' => 'crypto',
                'description' => 'Pago con Bitcoin, Ethereum u otras criptomonedas',
                'is_active' => false,
                'is_default' => false,
                'sort_order' => 6,
                'configuration' => json_encode([
                    'btc_address' => '',
                    'eth_address' => '',
                    'usdt_address' => ''
                ]),
                'supported_currencies' => ['BTC', 'ETH', 'USDT'],
                'transaction_fee_type' => null,
                'transaction_fee_amount' => 0,
                'transaction_fee_percentage' => 0,
                'min_amount' => 10,
                'max_amount' => null,
                'instructions' => 'Envíe el pago a la dirección de wallet indicada y comparta el hash de transacción.',
                'domain' => $currentDomain
            ]
        ];

        foreach ($gateways as $gatewayData) {
            PaymentGateway::updateOrCreate(
                ['slug' => $gatewayData['slug']],
                $gatewayData
            );
        }
    }
}
