<?php namespace Aero\Clouds\Models;

use Model;

/**
 * PaymentGateway Model
 */
class PaymentGateway extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \Aero\Clouds\Traits\LogsActivity;
    use \Aero\Clouds\Traits\DomainScoped;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aero_clouds_payment_gateways';

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'domain',
        'name',
        'slug',
        'type',
        'description',
        'is_active',
        'is_default',
        'sort_order',
        'configuration',
        'supported_currencies',
        'transaction_fee_type',
        'transaction_fee_amount',
        'transaction_fee_percentage',
        'min_amount',
        'max_amount',
        'logo',
        'instructions'
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required|max:255',
        'slug' => 'required|unique:aero_clouds_payment_gateways,slug|max:255|regex:/^[a-z0-9-]+$/',
        'type' => 'required|in:stripe,paypal,crypto,bank_transfer,qr_code,manual,other',
        'transaction_fee_type' => 'nullable|in:fixed,percentage,both',
        'transaction_fee_amount' => 'nullable|numeric|min:0',
        'transaction_fee_percentage' => 'nullable|numeric|min:0|max:100',
        'min_amount' => 'nullable|numeric|min:0',
        'max_amount' => 'nullable|numeric|min:0',
        'sort_order' => 'nullable|integer'
    ];

    /**
     * @var array Attributes that should be cast
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'supported_currencies' => 'json',
        'transaction_fee_amount' => 'decimal:2',
        'transaction_fee_percentage' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'sort_order' => 'integer'
    ];

    /**
     * @var array Attributes to be appended to the model's array form.
     */
    protected $appends = ['type_label'];

    /**
     * @var array File attachments
     */
    public $attachOne = [
        'logo' => 'System\Models\File'
    ];

    /**
     * Get the type label
     */
    public function getTypeLabelAttribute()
    {
        $types = [
            'stripe' => 'Stripe',
            'paypal' => 'PayPal',
            'crypto' => 'Cryptocurrency',
            'bank_transfer' => 'Bank Transfer',
            'qr_code' => 'QR Code Payment',
            'manual' => 'Manual Payment',
            'other' => 'Other'
        ];

        return $types[$this->type] ?? $this->type;
    }

    /**
     * Get configuration as array
     */
    public function getConfigurationDataAttribute()
    {
        if (empty($this->configuration)) {
            return [];
        }

        return is_string($this->configuration)
            ? json_decode($this->configuration, true)
            : $this->configuration;
    }

    /**
     * Mutator to ensure configuration is stored as JSON string
     */
    public function setConfigurationAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['configuration'] = json_encode($value);
        } elseif (is_string($value) && !empty($value)) {
            // Validate JSON string
            json_decode($value);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->attributes['configuration'] = $value;
            } else {
                $this->attributes['configuration'] = null;
            }
        } else {
            $this->attributes['configuration'] = null;
        }
    }

    /**
     * Get available gateway types
     */
    public static function getTypeOptions()
    {
        return [
            'stripe' => 'Stripe',
            'paypal' => 'PayPal',
            'crypto' => 'Cryptocurrency',
            'bank_transfer' => 'Bank Transfer',
            'qr_code' => 'QR Code Payment',
            'manual' => 'Manual Payment',
            'other' => 'Other'
        ];
    }

    /**
     * Get transaction fee type options
     */
    public static function getFeeTypeOptions()
    {
        return [
            'fixed' => 'Fixed Amount',
            'percentage' => 'Percentage',
            'both' => 'Fixed + Percentage'
        ];
    }

    /**
     * Scope to get only active gateways
     */
    public function scopeIsActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get default gateway
     */
    public function scopeIsDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Before save event
     */
    public function beforeSave()
    {
        // If this gateway is set as default, unset all others
        if ($this->is_default) {
            static::where('id', '!=', $this->id)->update(['is_default' => false]);
        }

        // Generate slug if not provided
        if (empty($this->slug) && !empty($this->name)) {
            $this->slug = \Str::slug($this->name);
        }
    }

    /**
     * Calculate transaction fee
     */
    public function calculateFee($amount)
    {
        $fee = 0;

        if ($this->transaction_fee_type === 'fixed') {
            $fee = $this->transaction_fee_amount ?? 0;
        } elseif ($this->transaction_fee_type === 'percentage') {
            $fee = ($amount * ($this->transaction_fee_percentage ?? 0)) / 100;
        } elseif ($this->transaction_fee_type === 'both') {
            $fee = ($this->transaction_fee_amount ?? 0) +
                   (($amount * ($this->transaction_fee_percentage ?? 0)) / 100);
        }

        return round($fee, 2);
    }

    /**
     * Get total amount including fee
     */
    public function getTotalWithFee($amount)
    {
        return round($amount + $this->calculateFee($amount), 2);
    }

    /**
     * Check if amount is within limits
     */
    public function isAmountValid($amount)
    {
        if ($this->min_amount && $amount < $this->min_amount) {
            return false;
        }

        if ($this->max_amount && $amount > $this->max_amount) {
            return false;
        }

        return true;
    }

    /**
     * Check if this is an offline payment gateway
     * Offline payments are those without API configuration
     */
    public function isOfflinePayment()
    {
        // If configuration is empty or null, it's an offline payment
        if (empty($this->configuration)) {
            return true;
        }

        // If configuration is a JSON string, decode and check
        if (is_string($this->configuration)) {
            $config = json_decode($this->configuration, true);
            return empty($config);
        }

        return false;
    }

    /**
     * Check if this is an online payment gateway
     */
    public function isOnlinePayment()
    {
        return !$this->isOfflinePayment();
    }
}
