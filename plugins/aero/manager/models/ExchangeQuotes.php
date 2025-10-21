<?php namespace Aero\Manager\Models;
use Illuminate\Support\Facades\Crypt;
use Model;
use Aero\Manager\Plugin;
use RainLab\User\Models\User;
/**
 * Model
 */
class ExchangeQuotes extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'aero_manager_exchange_quotes';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
    

    
    protected $jsonable = ['chat','fee_ext_detail'];
    
    public $belongsTo = [
        'from' => 'Aero\Manager\Models\ExchangeGateways',
        'to' => 'Aero\Manager\Models\ExchangeGateways',
        'user' => 'Rainlab\User\Models\User',
    ];
    
    public function beforeUpdate()
    {
        
//         $this->chat_alert = 0;
        
//         $this->fee_gateway = round($this->amount * 0.05, 2);        
        
//     if ($this->amount > 1 && $this->amount <= 50) {
//         $this->fee = 5;
//     } elseif ($this->amount >= 51 && $this->amount <= 99) {
//         $this->fee = 7;
//     } elseif ($this->amount >= 100 && $this->amount <= 150) {
//         $this->fee = $this->amount * 0.10;
//     } elseif ($this->amount >= 151 && $this->amount <= 249) {
//         $this->fee = $this->amount * 0.07;
//     } elseif ($this->amount >= 250 && $this->amount <= 349) {
//         $this->fee = $this->amount * 0.06;
//     } elseif ($this->amount >= 350) {
//         $this->fee = $this->amount * 0.05;
//     };        
        
//         if ($this->isDirty('chat') || $this->isDirty('status')) {
//         $message = "
// Hola, buenos días.
            
// Nuestro equipo de soporte ha respondido a su solicitud de cotización #".$this->id.". 
// Puede revisar los detalles en el siguiente enlace:
        
// https://pay.com.bo/dashboard/soporte/exchange/chat/".$this->identifier."
        
// Gracias por confiar en nosotros.
        
// Saludos cordiales,
// El equipo de soporte
//         ";
//          $user = User::where('id', $this->user_id)->first();
//          //Plugin::sendSmsWhatsapp($user->mobile,$message);
//           $vars = ['name' => "hola",'identifer' =>$this->identifier,'type'=>'exchange','invoice'=>$this->id];
//          Plugin::sendEmail( $user->email, 'dashboard::exchange-quote-updated',$vars);
//         }
    }
    

}
