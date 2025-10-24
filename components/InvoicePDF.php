<?php namespace Aero\Clouds\Components;

use Cms\Classes\ComponentBase;
use Aero\Clouds\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Redirect;
use Auth;

class InvoicePDF extends ComponentBase
{
    public $invoice;

    public function componentDetails()
    {
        return [
            'name'        => 'Invoice PDF',
            'description' => 'Displays invoice as PDF'
        ];
    }

    public function defineProperties()
    {
        return [
            'invoice' => [
                'title'       => 'Invoice Parameter',
                'description' => 'Invoice ID or number from URL',
                'default'     => '{{ :invoice }}',
                'type'        => 'string'
            ],
            'mode' => [
                'title'       => 'Display Mode',
                'description' => 'How to display the PDF',
                'type'        => 'dropdown',
                'default'     => 'inline',
                'options'     => [
                    'inline' => 'Display inline (in browser)',
                    'download' => 'Force download'
                ]
            ]
        ];
    }

    public function onRun()
    {
        $user = Auth::getUser();
        $invoiceParam = $this->property('invoice');

        // Try to find invoice by ID or invoice number
        $query = Invoice::with(['user', 'payment_gateway']);

        if ($user) {
            // If user is logged in, ensure they can only see their own invoices
            $query->where('user_id', $user->id);
        }

        $invoice = $query->where('id', $invoiceParam)
            ->orWhere('invoice_number', $invoiceParam)
            ->first();

        if (!$invoice) {
            return Redirect::to('/404');
        }

        $this->invoice = $invoice;

        // Parse invoice items
        $items = is_string($invoice->items) ? json_decode($invoice->items, true) : $invoice->items;

        // Generate PDF
        $pdf = Pdf::loadView('aero.clouds::pdf.invoice', [
            'invoice' => $invoice,
            'items' => $items,
            'user' => $invoice->user
        ]);

        // Set paper size
        $pdf->setPaper('letter', 'portrait');

        $filename = 'Factura-' . $invoice->invoice_number . '.pdf';

        // Check mode from query parameter or component property
        $mode = request()->get('mode', $this->property('mode'));

        if ($mode === 'download') {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    public function invoice()
    {
        return $this->invoice;
    }
}
