<?php namespace Aero\Manager\Models;

use Model;
use October\Rain\Database\Traits\Validation;
use October\Rain\Database\Traits\SoftDelete;
use Illuminate\Support\Facades\Crypt;

/**
 * Model
 */
class DashboardCards extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'aero_manager_dashboard_cards';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
    
    
    public function beforeCreate()
    {
        $this->cid = Crypt::encryptString(uniqid());
    }
    
}
