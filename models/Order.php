<?php namespace Aero\Clouds\Models;

use Model;
use Aero\Clouds\Models\EmailEvent;

class Order extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \Aero\Clouds\Traits\LogsActivity;
    use \Aero\Clouds\Traits\DomainScoped;

    protected $table = 'aero_clouds_orders';

    protected $fillable = [
        'domain',
        'user_id',
        'invoice_id',
        'order_date',
        'status',
        'items',
        'domains',
        'total_amount',
        'notes'
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'total_amount' => 'decimal:2'
    ];

    public $rules = [
        'user_id' => 'required|exists:users,id',
        'order_date' => 'required|date',
        'status' => 'required|in:pending,processing,completed,cancelled,refunded',
        'total_amount' => 'nullable|numeric|min:0'
    ];

    public $belongsTo = [
        'user' => [
            'RainLab\User\Models\User',
            'key' => 'user_id'
        ],
        'invoice' => [
            'Aero\Clouds\Models\Invoice',
            'key' => 'invoice_id'
        ]
    ];

    public $belongsToMany = [
        'plans' => [
            'Aero\Clouds\Models\Plan',
            'table' => 'aero_clouds_orders',
            'key' => 'id',
            'otherKey' => 'plan_id',
            'pivot' => ['quantity', 'billing_cycle', 'price']
        ],
        'extensions' => [
            'Aero\Clouds\Models\DomainExtension',
            'table' => 'aero_clouds_orders',
            'key' => 'id',
            'otherKey' => 'extension_id'
        ]
    ];

    /**
     * Get items attribute - handle both string and array from database
     */
    public function getItemsAttribute($value)
    {
        // If null or empty, return empty array
        if (empty($value)) {
            return [];
        }

        // If already an array, return it
        if (is_array($value)) {
            return $value;
        }

        // If string, decode it
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * Set items attribute - always store as JSON string
     */
    public function setItemsAttribute($value)
    {
        // If already a string, use it
        if (is_string($value)) {
            $this->attributes['items'] = $value;
            return;
        }

        // If array, encode it
        if (is_array($value)) {
            $this->attributes['items'] = json_encode($value);
            return;
        }

        $this->attributes['items'] = '[]';
    }

    /**
     * Get domains attribute - handle both string and array from database
     */
    public function getDomainsAttribute($value)
    {
        // If null or empty, return empty array
        if (empty($value)) {
            return [];
        }

        // If already an array, return it
        if (is_array($value)) {
            return $value;
        }

        // If string, decode it
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * Set domains attribute - always store as JSON string
     */
    public function setDomainsAttribute($value)
    {
        // If already a string, use it
        if (is_string($value)) {
            $this->attributes['domains'] = $value;
            return;
        }

        // If array, encode it
        if (is_array($value)) {
            $this->attributes['domains'] = json_encode($value);
            return;
        }

        $this->attributes['domains'] = '[]';
    }

    public function getStatusOptions()
    {
        return [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded'
        ];
    }

    public function getBillingCycleOptions()
    {
        return [
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly (3 months)',
            'semi_annually' => 'Semi-annually (6 months)',
            'annually' => 'Annually (12 months)',
            'biennially' => 'Biennially (24 months)',
            'triennially' => 'Triennially (36 months)'
        ];
    }

    public function getPlanIdOptions()
    {
        return Plan::orderBy('name')->lists('name', 'id');
    }

    public function getUserIdOptions()
    {
        $users = \RainLab\User\Models\User::orderBy('email')->get();
        $options = [];

        foreach ($users as $user) {
            $label = $user->email;
            if ($user->first_name || $user->last_name) {
                $label = trim($user->first_name . ' ' . $user->last_name) . ' (' . $user->email . ')';
            }
            $options[$user->id] = $label;
        }

        return $options;
    }

    public function getExtensionIdOptions()
    {
        return DomainExtension::where('is_available', true)
            ->orderBy('tld')
            ->lists('tld', 'id');
    }

    public function beforeSave()
    {
        // Always auto-calculate total from items
        if (!empty($this->items)) {
            $this->total_amount = $this->calculateTotal();
        }
    }

    public function calculateTotal()
    {
        $total = 0;

        if (is_array($this->items)) {
            foreach ($this->items as $item) {
                // Use the manually entered price
                if (isset($item['price'])) {
                    $total += $item['price'] * ($item['quantity'] ?? 1);
                }
            }
        }

        return $total;
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('order_date', 'desc');
    }

    public function afterCreate()
    {
        // Send order created notification immediately
        $this->sendOrderCreatedNotification();

        // Auto-generate invoice after order is created
        $invoice = $this->generateInvoice();

        // Send invoice created notification after generating invoice
        // Pass the invoice directly to avoid loading issues
        if ($invoice) {
            $this->sendInvoiceCreatedNotification($invoice);
        }

        // If order is created with "processing" status, provision servers
        if ($this->status === 'processing') {
            $this->provisionCloudServers();
        }
    }

    public function generateInvoice()
    {
        // Don't create invoice if one already exists
        if ($this->invoice_id) {
            return null;
        }

        // Map order items to invoice items
        $invoiceItems = [];

        if (is_array($this->items)) {
            foreach ($this->items as $item) {
                $description = 'Plan';
                $unitPrice = 0;

                // Get plan details
                if (isset($item['plan_id'])) {
                    $plan = Plan::find($item['plan_id']);
                    if ($plan) {
                        $description = $plan->name . ' - ' . ucfirst(str_replace('_', ' ', $item['billing_cycle'] ?? 'monthly'));

                        // Use the manually entered price
                        $unitPrice = $item['price'] ?? 0;
                    }
                }

                $invoiceItems[] = [
                    'description' => $description,
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $unitPrice
                ];
            }
        }

        // Calculate due date (30 days from order date)
        $dueDate = $this->order_date->copy()->addDays(30);

        // Create the invoice
        $invoice = Invoice::create([
            'user_id' => $this->user_id,
            'invoice_date' => $this->order_date,
            'due_date' => $dueDate,
            'status' => 'draft',
            'items' => $invoiceItems,
            'subtotal' => $this->total_amount ?? 0,
            'tax' => 0,
            'notes' => 'Auto-generated from Order #' . $this->id
        ]);

        // Link the invoice to the order
        $this->invoice_id = $invoice->id;
        $this->save();

        // Return the created invoice
        return $invoice;
    }

    /**
     * After update hook - detect status change to processing
     */
    public function afterUpdate()
    {
        // Check if status changed to 'processing'
        if ($this->status === 'processing' && $this->getOriginal('status') !== 'processing') {
            // Provision cloud servers
            $this->provisionCloudServers();

            // Send notifications (only if they weren't sent during creation)
            // Note: These will only send if the EmailEvent records exist and are enabled
            $this->sendOrderCreatedNotification();
            $this->sendInvoiceCreatedNotification();
        }
    }

    /**
     * Send order created notification email
     */
    protected function sendOrderCreatedNotification()
    {
        // Load user relation
        if (!$this->user) {
            return;
        }

        // Prepare context data for email template
        $context = [
            'order_id' => $this->id,
            'user' => [
                'name' => $this->user->full_name ?? $this->user->email,
                'email' => $this->user->email,
                'first_name' => $this->user->first_name ?? '',
            ],
            // Send DateTime object for Twig to format
            'order_date' => $this->order_date,
            'order_date_formatted' => $this->order_date->format('d/m/Y'),
            'status' => $this->status,
            // Send both numeric and formatted amounts
            'total_amount' => $this->total_amount,
            'total_amount_formatted' => number_format($this->total_amount, 2),
            'items' => $this->items,
            'domains' => $this->domains,
            // Add order object for template flexibility
            'order' => $this,
        ];

        // Trigger the email event
        EmailEvent::fire('order_created', $context, $this->user);
    }

    /**
     * Send invoice created notification email
     * @param Invoice|null $invoice Optional invoice object to avoid reloading
     */
    protected function sendInvoiceCreatedNotification($invoice = null)
    {
        // If invoice is not passed, try to load it
        if (!$invoice) {
            // Make sure we have an invoice
            if (!$this->invoice_id) {
                return;
            }

            // Load the invoice with user
            $invoice = $this->invoice()->with('user')->first();
            if (!$invoice) {
                return;
            }
        }

        // Make sure the invoice has a user loaded
        if (!$invoice->user) {
            $invoice->load('user');
        }

        if (!$invoice->user) {
            return;
        }

        // Prepare context data for email template
        $context = [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'order_id' => $this->id,
            'user' => [
                'name' => $invoice->user->full_name ?? $invoice->user->email,
                'email' => $invoice->user->email,
                'first_name' => $invoice->user->first_name ?? '',
            ],
            // Send DateTime objects for Twig to format
            'invoice_date' => $invoice->invoice_date,
            'invoice_date_formatted' => $invoice->invoice_date->format('d/m/Y'),
            'due_date' => $invoice->due_date,
            'due_date_formatted' => $invoice->due_date->format('d/m/Y'),
            // Send both numeric and formatted amounts
            'subtotal' => $invoice->subtotal,
            'subtotal_formatted' => number_format($invoice->subtotal, 2),
            'tax' => $invoice->tax,
            'tax_formatted' => number_format($invoice->tax, 2),
            'total' => $invoice->total,
            'total_formatted' => number_format($invoice->total, 2),
            'items' => $invoice->items,
            'status' => $invoice->status,
            // Add invoice object for template flexibility
            'invoice' => $invoice,
        ];

        // Trigger the email event
        EmailEvent::fire('invoice_created', $context, $invoice->user);
    }

    /**
     * Provision cloud servers from order items
     */
    public function provisionCloudServers()
    {
        if (!is_array($this->items)) {
            return;
        }

        foreach ($this->items as $item) {
            // Skip if no plan_id
            if (!isset($item['plan_id'])) {
                continue;
            }

            $plan = Plan::find($item['plan_id']);
            if (!$plan) {
                continue;
            }

            // Get quantity (default to 1)
            $quantity = $item['quantity'] ?? 1;

            // Get billing cycle to calculate expiration
            $billingCycle = $item['billing_cycle'] ?? 'monthly';
            $expirationDate = $this->calculateExpirationDate($billingCycle);

            // Create cloud servers based on quantity
            for ($i = 0; $i < $quantity; $i++) {
                // Generate server name
                $serverName = $this->generateServerName($plan, $i + 1, $quantity);

                Cloud::create([
                    'user_id' => $this->user_id,
                    'order_id' => $this->id,
                    'plan_id' => $plan->id,
                    'service_id' => $plan->service_id ?? null,
                    'name' => $serverName,
                    'status' => 'pending',
                    'created_date' => now(),
                    'expiration_date' => $expirationDate,
                    'auto_renew' => false,
                    'notes' => 'Auto-provisioned from Order #' . $this->id
                ]);
            }
        }
    }

    /**
     * Calculate expiration date based on billing cycle
     */
    protected function calculateExpirationDate($billingCycle)
    {
        $now = now();

        switch ($billingCycle) {
            case 'monthly':
                return $now->addMonth();
            case 'quarterly':
                return $now->addMonths(3);
            case 'semi_annually':
                return $now->addMonths(6);
            case 'annually':
                return $now->addYear();
            case 'biennially':
                return $now->addYears(2);
            case 'triennially':
                return $now->addYears(3);
            default:
                return $now->addMonth();
        }
    }

    /**
     * Generate server name
     */
    protected function generateServerName($plan, $index, $total)
    {
        $baseName = $plan->name;

        // If multiple servers, add index
        if ($total > 1) {
            return $baseName . ' #' . $index;
        }

        return $baseName;
    }
}
