<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.6;
        }

        .container {
            padding: 20px;
        }

        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #0ea5e9;
            padding-bottom: 20px;
        }

        .header-top {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .company-info {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }

        .company-info h1 {
            color: #0ea5e9;
            font-size: 24pt;
            margin-bottom: 5px;
        }

        .company-info p {
            color: #666;
            font-size: 9pt;
            margin: 2px 0;
        }

        .invoice-info {
            display: table-cell;
            width: 40%;
            text-align: right;
            vertical-align: top;
        }

        .invoice-info h2 {
            color: #333;
            font-size: 16pt;
            margin-bottom: 10px;
        }

        .invoice-info p {
            font-size: 9pt;
            margin: 3px 0;
        }

        .invoice-info .invoice-number {
            font-size: 12pt;
            font-weight: bold;
            color: #0ea5e9;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .status-draft { background: #e5e7eb; color: #374151; }
        .status-sent { background: #dbeafe; color: #1e40af; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-overdue { background: #fee2e2; color: #991b1b; }
        .status-cancelled { background: #f3f4f6; color: #6b7280; }
        .status-refunded { background: #fef3c7; color: #92400e; }

        .billing-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .billing-from, .billing-to {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 20px;
        }

        .billing-section h3 {
            font-size: 11pt;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }

        .billing-section p {
            font-size: 10pt;
            margin: 3px 0;
        }

        .billing-section .name {
            font-weight: bold;
            font-size: 11pt;
            color: #111;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table thead {
            background: #f3f4f6;
        }

        table thead th {
            padding: 12px 10px;
            text-align: left;
            font-size: 9pt;
            font-weight: bold;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #d1d5db;
        }

        table thead th.text-right {
            text-align: right;
        }

        table thead th.text-center {
            text-align: center;
        }

        table tbody td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10pt;
        }

        table tbody td.text-right {
            text-align: right;
        }

        table tbody td.text-center {
            text-align: center;
        }

        table tbody tr:last-child td {
            border-bottom: none;
        }

        .item-description {
            color: #111;
            font-weight: 500;
        }

        .item-details {
            color: #666;
            font-size: 8pt;
            margin-top: 3px;
        }

        .totals-section {
            float: right;
            width: 300px;
            margin-top: 20px;
        }

        .totals-section table {
            margin-bottom: 0;
        }

        .totals-section td {
            padding: 8px 10px;
            border: none;
        }

        .totals-section .label {
            text-align: right;
            font-weight: 500;
            color: #666;
        }

        .totals-section .amount {
            text-align: right;
            font-weight: bold;
            width: 120px;
        }

        .totals-section .subtotal-row td {
            border-bottom: 1px solid #e5e7eb;
        }

        .totals-section .tax-row td {
            border-bottom: 1px solid #e5e7eb;
        }

        .totals-section .total-row td {
            border-top: 2px solid #0ea5e9;
            font-size: 12pt;
            padding-top: 12px;
            padding-bottom: 12px;
            background: #f0f9ff;
        }

        .totals-section .total-row .label {
            color: #111;
        }

        .totals-section .total-row .amount {
            color: #0ea5e9;
        }

        .footer {
            clear: both;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .payment-info {
            margin-bottom: 20px;
        }

        .payment-info h3 {
            font-size: 11pt;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }

        .payment-info p {
            font-size: 9pt;
            color: #666;
            margin: 3px 0;
        }

        .notes {
            margin-top: 20px;
            padding: 15px;
            background: #f9fafb;
            border-left: 3px solid #0ea5e9;
        }

        .notes h3 {
            font-size: 10pt;
            color: #111;
            margin-bottom: 8px;
        }

        .notes p {
            font-size: 9pt;
            color: #666;
            line-height: 1.5;
        }

        .footer-text {
            text-align: center;
            font-size: 8pt;
            color: #9ca3af;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div class="company-info">
                    <h1>CLOUDS</h1>
                    <p><strong>Clouds Hosting Bolivia</strong></p>
                    <p>clouds.com.bo</p>
                    <p>Cochabamba, Bolivia</p>
                    <p>Teléfono: +591 (4) 123-4567</p>
                    <p>Email: facturacion@clouds.com.bo</p>
                </div>
                <div class="invoice-info">
                    <h2>FACTURA</h2>
                    <p class="invoice-number">{{ $invoice->invoice_number }}</p>
                    <p><strong>Fecha de Emisión:</strong> {{ $invoice->invoice_date->format('d/m/Y') }}</p>
                    <p><strong>Fecha de Vencimiento:</strong> {{ $invoice->due_date->format('d/m/Y') }}</p>
                    @php
                        $statusClass = 'status-' . str_replace('_', '-', $invoice->status);
                        $statusLabels = [
                            'draft' => 'Borrador',
                            'sent' => 'Enviada',
                            'paid' => 'Pagada',
                            'overdue' => 'Vencida',
                            'cancelled' => 'Cancelada',
                            'refunded' => 'Reembolsada'
                        ];
                    @endphp
                    <div class="status-badge {{ $statusClass }}">
                        {{ $statusLabels[$invoice->status] ?? $invoice->status }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Billing Information -->
        <div class="billing-section">
            <div class="billing-from">
                <h3>Facturado Por</h3>
                <p class="name">Clouds Hosting Bolivia</p>
                <p>NIT: 123456789</p>
                <p>Av. Principal #123</p>
                <p>Cochabamba, Bolivia</p>
                <p>facturacion@clouds.com.bo</p>
            </div>
            <div class="billing-to">
                <h3>Facturado A</h3>
                <p class="name">{{ $user->name }}</p>
                <p>{{ $user->email }}</p>
                @if($invoice->billing_address)
                    <p>{{ $invoice->billing_address }}</p>
                @endif
                @if($invoice->billing_city)
                    <p>{{ $invoice->billing_city }}, {{ $invoice->billing_country ?? 'Bolivia' }}</p>
                @endif
                @if($invoice->tax_id)
                    <p>NIT/CI: {{ $invoice->tax_id }}</p>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 50%;">Descripción</th>
                    <th class="text-center" style="width: 15%;">Cantidad</th>
                    <th class="text-right" style="width: 17.5%;">Precio Unitario</th>
                    <th class="text-right" style="width: 17.5%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>
                        <div class="item-description">{{ $item['description'] }}</div>
                        @if(!empty($item['details']))
                            <div class="item-details">{{ $item['details'] }}</div>
                        @endif
                    </td>
                    <td class="text-center">{{ $item['quantity'] }}</td>
                    <td class="text-right">Bs {{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                    <td class="text-right">Bs {{ number_format(($item['unit_price'] ?? 0) * $item['quantity'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <table>
                <tr class="subtotal-row">
                    <td class="label">Subtotal:</td>
                    <td class="amount">Bs {{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                @if($invoice->tax_rate > 0)
                <tr class="tax-row">
                    <td class="label">IVA ({{ $invoice->tax_rate }}%):</td>
                    <td class="amount">Bs {{ number_format($invoice->tax_amount, 2) }}</td>
                </tr>
                @endif
                @if($invoice->discount_amount > 0)
                <tr class="tax-row">
                    <td class="label">Descuento:</td>
                    <td class="amount">- Bs {{ number_format($invoice->discount_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td class="label">TOTAL:</td>
                    <td class="amount">Bs {{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <!-- Payment Information -->
            @if($invoice->payment_gateway || $invoice->payment_method)
            <div class="payment-info">
                <h3>Información de Pago</h3>
                @if($invoice->payment_method)
                    <p><strong>Método de Pago:</strong> {{ ucfirst($invoice->payment_method) }}</p>
                @endif
                @if($invoice->payment_gateway)
                    <p><strong>Pasarela:</strong> {{ $invoice->payment_gateway->name }}</p>
                @endif
                @if($invoice->paid_at)
                    <p><strong>Fecha de Pago:</strong> {{ $invoice->paid_at->format('d/m/Y H:i') }}</p>
                @endif
            </div>
            @endif

            <!-- Notes -->
            @if($invoice->notes)
            <div class="notes">
                <h3>Notas</h3>
                <p>{{ $invoice->notes }}</p>
            </div>
            @endif

            <!-- Terms -->
            <div class="notes">
                <h3>Términos y Condiciones</h3>
                <p>
                    El pago de esta factura debe realizarse antes de la fecha de vencimiento indicada.
                    Los servicios podrán ser suspendidos en caso de mora.
                    Para cualquier consulta sobre esta factura, por favor contacte a nuestro departamento de facturación.
                </p>
            </div>

            <!-- Footer Text -->
            <div class="footer-text">
                <p>Gracias por su preferencia | Clouds Hosting Bolivia | clouds.com.bo</p>
                <p>Este documento fue generado electrónicamente y es válido sin firma ni sello</p>
            </div>
        </div>
    </div>
</body>
</html>
