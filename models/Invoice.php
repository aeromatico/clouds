<?php namespace Aero\Clouds\Models;

use Model;

class Invoice extends Model
{
    use \October\Rain\Database\Traits\Validation;

    protected $table = 'aero_clouds_invoices';

    protected $fillable = [
        'user_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'status',
        'items',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'notes',
        'payment_method',
        'payment_date'
    ];

    protected $casts = [
        'invoice_date' => 'datetime',
        'due_date' => 'datetime',
        'payment_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2'
    ];

    public $rules = [
        'user_id' => 'required|exists:users,id',
        'invoice_number' => 'nullable|unique:aero_clouds_invoices,invoice_number',
        'invoice_date' => 'required|date',
        'due_date' => 'required|date',
        'status' => 'required|in:draft,sent,paid,overdue,cancelled,refunded',
        'subtotal' => 'nullable|numeric|min:0',
        'tax_rate' => 'nullable|numeric|min:0|max:100',
        'total_amount' => 'nullable|numeric|min:0',
        'payment_method' => 'nullable|in:cash,credit_card,debit_card,bank_transfer,paypal,stripe,other'
    ];

    public $belongsTo = [
        'user' => [
            'RainLab\User\Models\User',
            'key' => 'user_id'
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

    public function getPaymentMethodOptions()
    {
        return [
            'cash' => 'Cash',
            'credit_card' => 'Credit Card',
            'debit_card' => 'Debit Card',
            'bank_transfer' => 'Bank Transfer',
            'paypal' => 'PayPal',
            'stripe' => 'Stripe',
            'other' => 'Other'
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

        // Calculate tax amount
        if (!is_null($this->tax_rate) && !is_null($this->subtotal)) {
            $this->tax_amount = ($this->subtotal * $this->tax_rate) / 100;
        }

        // Calculate total
        if (!is_null($this->subtotal)) {
            $this->total_amount = $this->subtotal + ($this->tax_amount ?? 0);
        }
    }

    public function generateInvoiceNumber()
    {
        // Get the last invoice number
        $lastInvoice = static::orderBy('id', 'desc')->first();

        if ($lastInvoice && $lastInvoice->invoice_number) {
            // Extract the numeric part (remove leading zeros for calculation)
            $lastNumber = (int) $lastInvoice->invoice_number;
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
