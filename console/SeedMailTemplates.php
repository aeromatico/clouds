<?php namespace Aero\Clouds\Console;

use Illuminate\Console\Command;
use System\Models\MailTemplate;
use System\Models\MailLayout;

class SeedMailTemplates extends Command
{
    protected $signature = 'aero:seed-mail-templates';
    protected $description = 'Create mail templates for Aero.Clouds email notifications';

    public function handle()
    {
        $this->info('Creating mail templates...');

        // Get or create default layout
        $layout = MailLayout::firstOrCreate(
            ['code' => 'default'],
            [
                'name' => 'Default Layout',
                'content_html' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ subject }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    {{ content_html|raw }}

    <hr style="margin: 30px 0; border: none; border-top: 1px solid #ddd;">
    <p style="color: #999; font-size: 12px; text-align: center;">
        © {{ "now"|date("Y") }} clouds.com.bo - Todos los derechos reservados
    </p>
</body>
</html>',
                'content_text' => '{{ content_text }}'
            ]
        );

        $templates = [
            [
                'code' => 'user:new-invoice',
                'subject' => 'Nueva Factura #{{ invoice_number }}',
                'description' => 'Notificación de nueva factura para el usuario',
                'content_html' => '<h2>¡Hola {{ user.first_name }}!</h2>

<p>Se ha generado una nueva factura para tu pedido.</p>

<div style="background: #f5f5f5; padding: 20px; border-radius: 5px; margin: 20px 0;">
    <h3 style="margin-top: 0;">Detalles de la Factura</h3>
    <p><strong>Número de Factura:</strong> {{ invoice_number }}</p>
    <p><strong>Fecha:</strong> {{ invoice_date }}</p>
    <p><strong>Fecha de Vencimiento:</strong> {{ due_date }}</p>
    <p><strong>Estado:</strong> {{ status }}</p>
</div>

<h3>Resumen de Pago</h3>
<table style="width: 100%; border-collapse: collapse;">
    <tr>
        <td style="padding: 8px; border-bottom: 1px solid #ddd;">Subtotal:</td>
        <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">${{ subtotal }}</td>
    </tr>
    <tr>
        <td style="padding: 8px; border-bottom: 1px solid #ddd;">Impuestos:</td>
        <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">${{ tax }}</td>
    </tr>
    <tr>
        <td style="padding: 8px; font-weight: bold; font-size: 1.2em;">Total:</td>
        <td style="padding: 8px; text-align: right; font-weight: bold; font-size: 1.2em;">${{ total }}</td>
    </tr>
</table>

<p style="margin-top: 30px;">
    <a href="https://clouds.com.bo/account/invoices/{{ invoice_id }}"
       style="background: #0ea5e9; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
        Ver Factura
    </a>
</p>

<p style="color: #666; font-size: 14px; margin-top: 30px;">
    Si tienes alguna pregunta sobre esta factura, no dudes en contactarnos.
</p>',
                'content_text' => 'Hola {{ user.first_name }}!

Se ha generado una nueva factura para tu pedido.

Detalles de la Factura
Número de Factura: {{ invoice_number }}
Fecha: {{ invoice_date }}
Fecha de Vencimiento: {{ due_date }}
Estado: {{ status }}

Resumen de Pago:
Subtotal: ${{ subtotal }}
Impuestos: ${{ tax }}
Total: ${{ total }}

Ver factura: https://clouds.com.bo/account/invoices/{{ invoice_id }}

Si tienes alguna pregunta sobre esta factura, no dudes en contactarnos.

© {{ "now"|date("Y") }} clouds.com.bo',
            ],
            [
                'code' => 'backend:new-invoice',
                'subject' => '[Admin] Nueva Factura #{{ invoice_number }}',
                'description' => 'Notificación de nueva factura para administradores',
                'content_html' => '<h2>Nueva Factura Generada</h2>

<p>Se ha generado una nueva factura en el sistema.</p>

<div style="background: #f5f5f5; padding: 20px; border-radius: 5px; margin: 20px 0;">
    <h3 style="margin-top: 0;">Detalles de la Factura</h3>
    <p><strong>Número de Factura:</strong> {{ invoice_number }}</p>
    <p><strong>Cliente:</strong> {{ user.name }} ({{ user.email }})</p>
    <p><strong>Fecha:</strong> {{ invoice_date }}</p>
    <p><strong>Fecha de Vencimiento:</strong> {{ due_date }}</p>
    <p><strong>Estado:</strong> {{ status }}</p>
    <p><strong>Pedido ID:</strong> #{{ order_id }}</p>
</div>

<h3>Resumen Financiero</h3>
<table style="width: 100%; border-collapse: collapse;">
    <tr>
        <td style="padding: 8px; border-bottom: 1px solid #ddd;">Subtotal:</td>
        <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">${{ subtotal }}</td>
    </tr>
    <tr>
        <td style="padding: 8px; border-bottom: 1px solid #ddd;">Impuestos:</td>
        <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">${{ tax }}</td>
    </tr>
    <tr>
        <td style="padding: 8px; font-weight: bold; font-size: 1.2em;">Total:</td>
        <td style="padding: 8px; text-align: right; font-weight: bold; font-size: 1.2em;">${{ total }}</td>
    </tr>
</table>

<p style="margin-top: 30px;">
    <a href="https://clouds.com.bo/backend/aero/clouds/invoices/update/{{ invoice_id }}"
       style="background: #0ea5e9; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
        Ver en Backend
    </a>
</p>',
                'content_text' => 'Nueva Factura Generada

Detalles de la Factura
Número de Factura: {{ invoice_number }}
Cliente: {{ user.name }} ({{ user.email }})
Fecha: {{ invoice_date }}
Fecha de Vencimiento: {{ due_date }}
Estado: {{ status }}
Pedido ID: #{{ order_id }}

Resumen Financiero:
Subtotal: ${{ subtotal }}
Impuestos: ${{ tax }}
Total: ${{ total }}

Ver en backend: https://clouds.com.bo/backend/aero/clouds/invoices/update/{{ invoice_id }}',
            ],
            [
                'code' => 'user:new-order',
                'subject' => 'Confirmación de Pedido #{{ order_id }}',
                'description' => 'Confirmación de nuevo pedido para el usuario',
                'content_html' => '<h2>¡Gracias por tu pedido, {{ user.first_name }}!</h2>

<p>Hemos recibido tu pedido y lo estamos procesando.</p>

<div style="background: #f5f5f5; padding: 20px; border-radius: 5px; margin: 20px 0;">
    <h3 style="margin-top: 0;">Detalles del Pedido</h3>
    <p><strong>Número de Pedido:</strong> #{{ order_id }}</p>
    <p><strong>Fecha:</strong> {{ order_date }}</p>
    <p><strong>Estado:</strong> {{ status }}</p>
    <p><strong>Total:</strong> ${{ total }}</p>
</div>

<p>Recibirás una notificación cuando tu pedido sea procesado y tus servicios estén listos.</p>

<p style="margin-top: 30px;">
    <a href="https://clouds.com.bo/account/orders/{{ order_id }}"
       style="background: #0ea5e9; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
        Ver Pedido
    </a>
</p>',
                'content_text' => 'Gracias por tu pedido, {{ user.first_name }}!

Hemos recibido tu pedido y lo estamos procesando.

Detalles del Pedido
Número de Pedido: #{{ order_id }}
Fecha: {{ order_date }}
Estado: {{ status }}
Total: ${{ total }}

Recibirás una notificación cuando tu pedido sea procesado y tus servicios estén listos.

Ver pedido: https://clouds.com.bo/account/orders/{{ order_id }}',
            ],
            [
                'code' => 'backend:new-order',
                'subject' => '[Admin] Nuevo Pedido #{{ order_id }}',
                'description' => 'Notificación de nuevo pedido para administradores',
                'content_html' => '<h2>Nuevo Pedido Recibido</h2>

<p>Se ha recibido un nuevo pedido en el sistema.</p>

<div style="background: #f5f5f5; padding: 20px; border-radius: 5px; margin: 20px 0;">
    <h3 style="margin-top: 0;">Detalles del Pedido</h3>
    <p><strong>Número de Pedido:</strong> #{{ order_id }}</p>
    <p><strong>Cliente:</strong> {{ user.name }} ({{ user.email }})</p>
    <p><strong>Fecha:</strong> {{ order_date }}</p>
    <p><strong>Estado:</strong> {{ status }}</p>
    <p><strong>Total:</strong> ${{ total }}</p>
</div>

<p style="margin-top: 30px;">
    <a href="https://clouds.com.bo/backend/aero/clouds/orders/update/{{ order_id }}"
       style="background: #0ea5e9; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
        Ver en Backend
    </a>
</p>',
                'content_text' => 'Nuevo Pedido Recibido

Detalles del Pedido
Número de Pedido: #{{ order_id }}
Cliente: {{ user.name }} ({{ user.email }})
Fecha: {{ order_date }}
Estado: {{ status }}
Total: ${{ total }}

Ver en backend: https://clouds.com.bo/backend/aero/clouds/orders/update/{{ order_id }}',
            ]
        ];

        foreach ($templates as $templateData) {
            $template = MailTemplate::firstOrNew(['code' => $templateData['code']]);

            if ($template->exists) {
                $this->warn('  - ' . $templateData['code'] . ' already exists');
            } else {
                $template->fill([
                    'subject' => $templateData['subject'],
                    'description' => $templateData['description'],
                    'content_html' => $templateData['content_html'],
                    'content_text' => $templateData['content_text'],
                    'layout_id' => $layout->id,
                    'is_custom' => true
                ]);
                $template->save();
                $this->info('  ✓ ' . $templateData['code'] . ' created');
            }
        }

        $this->info('✓ All mail templates created successfully!');
    }
}
