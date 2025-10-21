<?php namespace Aero\Manager\Models;

use Model;
use DB;
use Aero\Manager\Plugin;
use RainLab\User\Models\User;
/**
 * Model
 */
class DashboardServices extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'aero_manager_dashboard_services';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
    
    public $belongsTo = [
        'wallet' => 'Aero\Manager\Models\Wallet',
        'docs' => 'Aero\Manager\Models\Docs'
    ];
    
    public function afterCreate()
    {
        $order = DB::table('aero_manager_wallet')
        ->where('id', $this->wallet_id)
        ->first();
        $user = User::find($order->user_id);
        $firstName = explode(' ', trim($user->name))[0] ?? '';
        
        $vars = [
            'panel' => $this->panel,
            'first_name_client' => $firstName,
            'user' => $this->username,
            'pass' => $this->password,
        ];
        
        Plugin::sendEmail($user->email, 'dashboard::create-service', $vars);
    }
    
}
