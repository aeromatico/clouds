<?php namespace Aero\Manager;

use System\Classes\PluginBase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Aero\Manager\Models\Plans;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
        return [
            'Aero\Manager\Components\CustomRegistration' => 'customRegistration'
        ];
    }

    public function registerSettings()
    {
    }
    
    public function registerMarkupTags()
    {
        return [
            'functions' => [
                'transPeriod' => function ($text) {
                    
                    $source = array("Mensual", "Trimestral", "Anual", "2 años", "3 años", "Semestral");
                    $trans   = array("monthly", "quarterly", "annually","biennially","triennially","semiannually");
                    return str_replace($source, $trans, $text); 
                    
                },
                
                'transCurrency' => function ($text) {
                    
                    $source = array("BOB", "USD", "ARS");
                    $trans   = array("Bs", 'US$', '$');
                    return str_replace($source, $trans, $text); 
                    
                },
                
                'pricingWhcms' => function($id,$currency){
                     $rows = DB::connection('mysql-whmcs')->table('tblpricing')
                ->where('currency', $currency)
                ->where('relid', $id)->select('monthly', 'quarterly', 'semiannually','annually','biennially')
                ->get();
                     
                     //select('select monthly,quarterly,semiannually,annually,biennially from tblpricing where currency=:currency and relid=:name', ['name' => $id,'currency'=>$currency]);
                 $obj = json_decode($rows);
               
                    return  $obj;

                    
                },
                
                'transWhcms' => function ($text) {
                    
                    $data;
                    
                    switch ($text) {
                      case "1":
                        $data="Mensual";
                        break;
                      case "2":
                        $data= "Trimestral";
                        break;
                      case "3":
                        $data= "Semestral";
                        break;
                      case "4":
                        $data= "Anual";
                        break;
                      case "5":
                         $data= "Bienal";
                        break;
                      default:
                       $data=$text;
                    }
                    
                    return $data;
                },
                
                'Whcms' => function ($array){
                    // Agregamos los parámetros adicionales al arreglo recibido
                    $array['username'] = '5PJiQmxvEzIzx3pArWzNSmIeu7CwIV0T';
                    $array['password'] = 'z6mu36HTSJ8TldTx0QZE070Pi0zM5coO';
                    $array['accesskey'] = 'RdCrwqjb4AnVz5Eqtvf2BDRsp3wCGUlb';
                    
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://clientes.boliviahost.com/includes/api.php");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS,
                        http_build_query($array)
                    );
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $response = curl_exec($ch);
                    curl_close($ch);
                    $data = json_decode($response);
                    
                    return $data;
                },
                
                'services_type' => function ($text) {
                    $slug='';
                    switch ($text) {
                    case 'cloud':
                        $slug = 'cloud';
                        break;
                    case 'saas':
                        $slug = 'servicio';  
                        break;
                    case 'promo':
                        $slug = 'promo';  
                        break;
                    case 'resellers':
                        $slug = 'resellers';  
                        break; 
                    case 'development':
                        $slug = 'desarrollo';  
                        break;                         
                    }

                   return $slug;
                },
                
                'tooltip' => function ($text) {

                    return '<tooltip class="tooltip tooltip-black" data-tip="'. $text .'"><i class="fa-regular fa-circle-question cursor-pointer"></i></tooltip>';
                    
                },                
                
               'name_change' => function ($array) {
                    if ($array['alias']) {
                        $array['name'] = $array['alias'];
                    }
                },
                
                'slug' => function ($text) {
                    
                    $string = strtolower($text);
                
                    $string = str_replace(' ', '-', $string);
                
                    $string = preg_replace('/[^a-z0-9-]+/', '-', $string);
                
                    $string = trim($string, '-');
                
                    $string = substr($string, 0, 255);
                
                    return $string;
                },
                
                'services_category_docs'=> function ($category_id) {
                    
               $json_data = DB::connection('mysql-whmcs')
                    ->table('tblknowledgebase')
                    ->join('tblknowledgebaselinks', 'tblknowledgebase.id', '=', 'tblknowledgebaselinks.articleid')
                    ->join('tblknowledgebasecats', 'tblknowledgebaselinks.categoryid', '=', 'tblknowledgebasecats.id')
                    ->where('tblknowledgebasecats.id', $category_id)
                    ->select('tblknowledgebase.id', 'tblknowledgebase.views', 'tblknowledgebase.private', 'tblknowledgebase.title', 'tblknowledgebasecats.name AS category', 'tblknowledgebase.article')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'views' => $item->views,
                            'private' => $item->private,
                            'title' => $item->title,
                            'category' => $item->category,
                            'article' => $item->article,
                        ];
                    })
                    ->toArray();
                
                return $json_data;

                },
                

                'exploit_feature'=> function ($cadena) {
                  if (strpos($cadena, '-') !== false) {
                        $array = explode('-', $cadena);
                        $segundo_elemento = trim($array[1]); 
                        echo $segundo_elemento;
                    } else {
                        echo $cadena;
                    }
                },
                
                'gallery_filter'=> function ($array) {
                    $identifiersUnicos = array_unique(array_column($array, 'identifier')); 
                    return $identifiersUnicos;
                },
                
                'appareance_accent'=> function ($accent) {
                   $text="";
                   if($accent){
                       $text=$accent;
                   }
                   else{
                       $text="bg-blue-500 text-white";
                   }
                   return $text;
                },
                
                'getPlansWithFilteredPricing' => function ($plans) {
                    $filteredPlans = [];
                
                    foreach ($plans as $plan) {
                        $filteredPricing = [];
                
                        if (isset($plan['pricing']) && is_array($plan['pricing'])) {
                            $pricingByPeriod = [];
                
                            foreach ($plan['pricing'] as $price) {
                                if (isset($price['domain']) && $price['domain'] === $_SERVER['HTTP_HOST']) {
                                    $period = $price['price_period'];
                
                                     if (isset($pricingByPeriod[$period])) {
                                        if ($price['price_currency'] === 'Créditos') {
                                      
                                            $pricingByPeriod[$period]['credits'] = $price['price'];
                                        }
                                    } else {

                                        $pricingByPeriod[$period] = $price;
                                    }
                                }
                            }
                
                            foreach ($pricingByPeriod as $price) {
                                $filteredPricing[] = $price;
                            }
                        }
                
                        $plan['pricing'] = $filteredPricing;
                        $filteredPlans[] = $plan;
                    }
                
                    return $filteredPlans;
                },

            ]
        ];
    }
    
public function boot()
{
    // Registrar middleware para API tokens
    if (class_exists('\Aero\Manager\Middleware\CheckApiToken')) {
        $this->app['router']->aliasMiddleware('aero.manager.api_token', \Aero\Manager\Middleware\CheckApiToken::class);
    }

    // Registrar las rutas del plugin
    $this->app->booted(function () {
        $routePath = __DIR__ . '/routes.php';
        if (file_exists($routePath)) {
            \Illuminate\Support\Facades\Route::group([
                'namespace' => 'Aero\Manager\Controllers'
            ], function () use ($routePath) {
                require $routePath;
            });
        }
    });
}

    public static function Whcms($array)
    {
        // Agregamos los parámetros adicionales al arreglo recibido
        $array['username'] = '5PJiQmxvEzIzx3pArWzNSmIeu7CwIV0T';
        $array['password'] = 'z6mu36HTSJ8TldTx0QZE070Pi0zM5coO';
        $array['accesskey'] = 'RdCrwqjb4AnVz5Eqtvf2BDRsp3wCGUlb';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://clientes.boliviahost.com/includes/api.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($array));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response);
        
        return $data;
    }
    
    public static function sendEmail($email, $template, $vars) {
        $vars['email'] = $email;
    
        Mail::send($template, $vars, function($message) use ($vars) {
            $message->to($vars['email'], 'nombre');
        });
    }
    
    public static function sendSmsWhatsapp($number, $message) {

       $url = 'https://app.whats.lat/api/user/v2/send_message'; // Replace with your domain endpoint
      
        $body = [
            'client_id' => 'eyJ1aWQiOiJEWHhMcmVBOGtNYUExbGI0VWFFdFh5eUhVRURGR1FDMSIsImNsaWVudF9pZCI6IkJvbGl2aWEgSG9zdCJ9', // Client ID here
            'mobile' => $number,
            'text' => $message,
        ];
      
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1aWQiOiJEWHhMcmVBOGtNYUExbGI0VWFFdFh5eUhVRURGR1FDMSIsInJvbGUiOiJ1c2VyIiwiaWF0IjoxNzIxNjU0NTQzfQ.6TXXNZ4-9PtVBc3B1dzR3eL2ITrCPguXHNf73QpsnWs'; // Your API keys here
      
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
        ];
      
        try {
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
      
            $response = curl_exec($curl);
      
            if ($response === false) {
                echo "Lo sentimos, ocurrió un error al intentar enviar el mensaje de WhatsApp";
            }
      
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($httpCode >= 400) {
              echo "Lo sentimos, ocurrió un error al intentar enviar el mensaje de WhatsApp";
            }
      
            $data = json_decode($response, true);
        } catch (Exception $error) {
           echo "Lo sentimos, ocurrió un error al intentar enviar el mensaje de WhatsApp";
        }


    }


}
