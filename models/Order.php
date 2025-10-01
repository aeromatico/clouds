<?php namespace Aero\Clouds\Models;

use Model;

class Order extends Model
{
    use \October\Rain\Database\Traits\Validation;

    protected $table = 'aero_clouds_orders';

    protected $fillable = [
        'user_id',
        'invoice_id',
        'order_date',
        'status',
        'items',
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
        // Auto-generate invoice after order is created
        $this->generateInvoice();
    }

    public function generateInvoice()
    {
        // Don't create invoice if one already exists
        if ($this->invoice_id) {
            return;
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
            'tax_rate' => 0,
            'notes' => 'Auto-generated from Order #' . $this->id
        ]);

        // Link the invoice to the order
        $this->invoice_id = $invoice->id;
        $this->save();
    }
}
