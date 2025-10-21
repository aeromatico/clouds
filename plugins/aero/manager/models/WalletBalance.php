<?php namespace Aero\Manager\Models;

use Model;

/**
 * Model
 */
class WalletBalance extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aero_manager_wallet_balance';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
    

}
