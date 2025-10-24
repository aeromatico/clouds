<?php namespace Aero\Clouds\Models;

use Model;

class Invoice extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \Aero\Clouds\Traits\LogsActivity;
    use \Aero\Clouds\Traits\DomainScoped;

    protected $table = 'aero_clouds_invoices';

    protected $fillable = [
        'domain',
        'user_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'status',
        'items',
        'subtotal',
        'tax',
        'total',
        'notes',
        'payment_gateway_id'
    ];

    protected $casts = [
        'invoice_date' => 'datetime',
        'due_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    public $rules = [
        'user_id' => 'required|exists:users,id',
        'invoice_number' => 'nullable|unique:aero_clouds_invoices,invoice_number',
        'invoice_date' => 'required|date',
        'due_date' => 'required|date',
        'status' => 'required|in:draft,sent,paid,overdue,cancelled,refunded',
        'subtotal' => 'nullable|numeric|min:0',
        'tax' => 'nullable|numeric|min:0',
        'total' => 'nullable|numeric|min:0'
    ];

    public $belongsTo = [
        'user' => [
            'RainLab\User\Models\User',
            'key' => 'user_id'
        ],
        'payment_gateway' => [
            'Aero\Clouds\Models\PaymentGateway',
            'key' => 'payment_gateway_id'
        ]
    ];

    public $hasMany = [
        'orders' => [
            'Aero\Clouds\Models\Order',
            'key' => 'invoice_id'
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
            'draft' => 'Draft',
            'sent' => 'Sent',
            'paid' => 'Paid',
            'overdue' => 'Overdue',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded'
        ];
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

    public function beforeCreate()
    {
        // Auto-generate invoice number if not provided
        if (empty($this->invoice_number)) {
            $this->invoice_number = $this->generateInvoiceNumber();
        }
    }

    public function beforeSave()
    {
        // Always auto-calculate subtotal from items
        if (!empty($this->items)) {
            $this->subtotal = $this->calculateSubtotal();
        } else {
            // If no items, set subtotal to 0
            $this->subtotal = 0;
        }

        // If tax is not set, default to 0
        if (is_null($this->tax)) {
            $this->tax = 0;
        }

        // Calculate total
        if (!is_null($this->subtotal)) {
            $this->total = $this->subtotal + ($this->tax ?? 0);
        }
    }

    public function generateInvoiceNumber()
    {
        // IMPORTANTE: Usar withoutDomainScope() para obtener la última factura de TODOS los dominios
        // Esto evita duplicados ya que invoice_number es único globalmente
        $lastInvoice = static::withoutDomainScope()->orderBy('id', 'desc')->first();

        if ($lastInvoice && $lastInvoice->invoice_number) {
            // Extract numeric part from various formats:
            // '0001', '0002' -> 1, 2
            // 'INV-000016' -> 16
            // '1234' -> 1234
            $invoiceNum = $lastInvoice->invoice_number;

            // Remove any non-numeric characters and get the number
            $numericPart = preg_replace('/[^0-9]/', '', $invoiceNum);
            $lastNumber = $numericPart ? (int) $numericPart : 0;

            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        // Return formatted as 0001, 0002, 0003, etc.
        return str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function calculateSubtotal()
    {
        $subtotal = 0;

        if (is_array($this->items)) {
            foreach ($this->items as $item) {
                $price = $item['unit_price'] ?? 0;
                $quantity = $item['quantity'] ?? 1;
                $subtotal += $price * $quantity;
            }
        }

        return $subtotal;
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('due_date', '<', now());
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('invoice_date', 'desc');
    }

    public function getIsOverdueAttribute()
    {
        return $this->status !== 'paid'
            && $this->status !== 'cancelled'
            && $this->due_date < now();
    }
}
