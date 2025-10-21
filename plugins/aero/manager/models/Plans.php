<?php namespace Aero\Manager\Models;

use October\Rain\Database\Model;

/**
 * Model
 */
class Plans extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

     use \October\Rain\Database\Traits\Sortable;
     
    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'aero_manager_plans';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
    
    public function beforeCreate(){
        $this->domain = str_replace('wwww.','',$_SERVER['HTTP_HOST']);
    }


public function beforeSave()
{
    // Definir las reglas de dominios y monedas con factores opcionales
    $domainRules = [
        'boliviahost.com' => [
            'currencies' => ['BOB', 'USD'],
            'factor' => null // Sin factor para el dominio base
        ],
        'llajwa.club' => [
            'currencies' => ['BOB', 'USD', 'Créditos'],
            'factor' => [
                'value' => 5,
                'type' => 'percentage' // +5%
            ]
        ],
        'clouds.com.ar' => [
            'currencies' => ['ARS', 'USD'],
            'factor' => [
                'value' => -20,
                'type' => 'amount' // -20 unidades
            ]
        ],
        'cloud.difunde.cloud' => [
            'currencies' => ['USD'],
            'factor' => [
                'value' => 10,
                'type' => 'percentage' // +10%
            ]
        ]
    ];
    
    // Solo procesar si el campo pricing existe y tiene contenido
    if (!empty($this->pricing)) {
        $currentPricing = is_string($this->pricing) ? json_decode($this->pricing, true) : $this->pricing;

        if (!is_array($currentPricing)) {
            // Ensure pricing is always an array if it's not empty
            $this->pricing = [];
            return;
        }
        
        // Obtener cotizaciones del endpoint
        $exchangeRates = $this->getExchangeRates();
        
        if (!$exchangeRates) {
            return; // Si no se pueden obtener las cotizaciones, no procesar
        }
        
        // Buscar precios en USD del dominio boliviahost.com
        $usdPlansBoliviahost = [];
        foreach ($currentPricing as $plan) {
            if (isset($plan['domain']) && $plan['domain'] === 'boliviahost.com' && 
                isset($plan['price_currency']) && $plan['price_currency'] === 'USD') {
                $usdPlansBoliviahost[] = $plan;
            }
        }
        
        // Si encontramos planes en USD de boliviahost.com, replicamos para otros dominios
        if (!empty($usdPlansBoliviahost)) {
            $newPricing = $currentPricing; // Mantenemos los precios existentes
            
            foreach ($usdPlansBoliviahost as $basePlan) {
                foreach ($domainRules as $domain => $domainConfig) {
                    $currencies = $domainConfig['currencies'];
                    $factor = isset($domainConfig['factor']) ? $domainConfig['factor'] : null;
                    
                    foreach ($currencies as $currency) {
                        // Para boliviahost.com, solo generar BOB (ya que USD ya existe)
                        if ($domain === 'boliviahost.com' && $currency === 'USD') {
                            continue;
                        }
                        
                        // Verificar si ya existe este plan específico
                        $exists = false;
                        foreach ($newPricing as $existingPlan) {
                            if (isset($existingPlan['domain']) && $existingPlan['domain'] === $domain &&
                                isset($existingPlan['price_currency']) && $existingPlan['price_currency'] === $currency &&
                                isset($existingPlan['price_period']) && $existingPlan['price_period'] === $basePlan['price_period']) {
                                $exists = true;
                                break;
                            }
                        }
                        
                        // Si no existe, crear el nuevo plan
                        if (!$exists) {
                            $newPlan = $basePlan; // Copiar el plan base
                            $newPlan['domain'] = $domain;
                            $newPlan['price_currency'] = $currency;
                            
                            // Convertir precios según la moneda
                            $convertedPrice = $this->convertPrice($basePlan['price'], $currency, $exchangeRates);
                            $convertedSetupPrice = $this->convertPrice($basePlan['price_setup'], $currency, $exchangeRates);
                            
                            // Aplicar factor si existe
                            if ($factor) {
                                $convertedPrice = $this->applyPriceFactor($convertedPrice, $factor);
                                $convertedSetupPrice = $this->applyPriceFactor($convertedSetupPrice, $factor);
                            }
                            
                            $newPlan['price'] = $convertedPrice;
                            $newPlan['price_setup'] = $convertedSetupPrice;
                            
                            $newPricing[] = $newPlan;
                        }
                    }
                }
            }
            
            // Actualizar el campo pricing
            $this->pricing = $newPricing;
        }
    }
}

/**
 * Obtiene las cotizaciones del endpoint
 */
private function getExchangeRates()
{
    try {
        $response = file_get_contents('https://boliviahost.com/apis/public/dolar-blue');
        if ($response === false) {
            error_log('Error: No se pudo obtener respuesta del endpoint de cotizaciones');
            return null;
        }
        
        $data = json_decode($response, true);
        if (!$data || !isset($data['data'])) {
            error_log('Error: Estructura de datos inválida del endpoint');
            return null;
        }
        
        // Debug: Log de las cotizaciones obtenidas
        $rates = $data['data'];
        if (isset($rates['USDT']['value'])) {
            error_log("USDT value obtenido: " . $rates['USDT']['value']);
        }
        if (isset($rates['PEN']['blue'])) {
            error_log("PEN Blue rate obtenido: " . $rates['PEN']['blue']);
        }
        if (isset($rates['ARS']['blue'])) {
            error_log("ARS Blue rate obtenido: " . $rates['ARS']['blue']);
        }
        
        return $rates;
    } catch (Exception $e) {
        error_log('Excepción al obtener cotizaciones: ' . $e->getMessage());
        return null;
    }
}

/**
 * Convierte el precio de USD a la moneda especificada
 */
private function convertPrice($usdPrice, $targetCurrency, $exchangeRates)
{
    if ($usdPrice == 0) {
        return 0;
    }
    
    $convertedPrice = 0;
    
    switch ($targetCurrency) {
        case 'USD':
            return $usdPrice; // Sin conversión
            
        case 'BOB':
            // USD > BOB usando el valor USDT (equivalente a USD paralelo)
            if (isset($exchangeRates['USDT']['value']) && $exchangeRates['USDT']['value'] > 0) {
                $convertedPrice = $usdPrice * $exchangeRates['USDT']['value'];
                error_log("Conversión BOB: {$usdPrice} USD × {$exchangeRates['USDT']['value']} USDT = {$convertedPrice}");
            } 
            // Fallback: usar USD blue si USDT no está disponible
            elseif (isset($exchangeRates['USD']['blue']) && $exchangeRates['USD']['blue'] > 0) {
                $convertedPrice = $usdPrice * $exchangeRates['USD']['blue'];
                error_log("Conversión BOB (fallback): {$usdPrice} USD × {$exchangeRates['USD']['blue']} USD blue = {$convertedPrice}");
            } 
            else {
                error_log('ERROR: No se encontró USDT value ni USD blue en el endpoint');
                error_log('Rates disponibles: ' . json_encode($exchangeRates));
                return $usdPrice;
            }
            break;
            
        case 'Créditos':
            // USD > BOB > PEN usando USDT value (o USD blue) y PEN blue
            $usdToBobRate = 0;
            if (isset($exchangeRates['USDT']['value']) && $exchangeRates['USDT']['value'] > 0) {
                $usdToBobRate = $exchangeRates['USDT']['value'];
            } elseif (isset($exchangeRates['USD']['blue']) && $exchangeRates['USD']['blue'] > 0) {
                $usdToBobRate = $exchangeRates['USD']['blue'];
            }
            
            if ($usdToBobRate > 0 && isset($exchangeRates['PEN']['blue']) && $exchangeRates['PEN']['blue'] > 0) {
                $bobAmount = $usdPrice * $usdToBobRate;
                $convertedPrice = $bobAmount / $exchangeRates['PEN']['blue'];
                error_log("Conversión Créditos: {$usdPrice} USD × {$usdToBobRate} = {$bobAmount} BOB ÷ {$exchangeRates['PEN']['blue']} PEN blue = {$convertedPrice}");
            } else {
                error_log('ERROR: No se encontraron rates para conversión a Créditos');
                error_log('USD to BOB rate: ' . $usdToBobRate . ', PEN blue: ' . ($exchangeRates['PEN']['blue'] ?? 'N/A'));
                return $usdPrice;
            }
            break;
            
        case 'ARS':
            // USD > BOB > ARS usando USDT value (o USD blue) y ARS blue
            $usdToBobRate = 0;
            if (isset($exchangeRates['USDT']['value']) && $exchangeRates['USDT']['value'] > 0) {
                $usdToBobRate = $exchangeRates['USDT']['value'];
            } elseif (isset($exchangeRates['USD']['blue']) && $exchangeRates['USD']['blue'] > 0) {
                $usdToBobRate = $exchangeRates['USD']['blue'];
            }
            
            if ($usdToBobRate > 0 && isset($exchangeRates['ARS']['blue']) && $exchangeRates['ARS']['blue'] > 0) {
                $bobAmount = $usdPrice * $usdToBobRate;
                $convertedPrice = $bobAmount / $exchangeRates['ARS']['blue'];
                error_log("Conversión ARS: {$usdPrice} USD × {$usdToBobRate} = {$bobAmount} BOB ÷ {$exchangeRates['ARS']['blue']} ARS blue = {$convertedPrice}");
            } else {
                error_log('ERROR: No se encontraron rates para conversión a ARS');
                error_log('USD to BOB rate: ' . $usdToBobRate . ', ARS blue: ' . ($exchangeRates['ARS']['blue'] ?? 'N/A'));
                return $usdPrice;
            }
            break;
            
        default:
            error_log("Moneda no soportada: {$targetCurrency}");
            return $usdPrice;
    }
    
    // Si no se pudo convertir, mantener el precio original
    if ($convertedPrice <= 0) {
        error_log("Error en conversión: precio convertido es 0 o negativo para {$targetCurrency}");
        return $usdPrice;
    }
    
    // Convertir a entero y terminar en 9
    return $this->formatPriceEndingIn9($convertedPrice);
}

/**
 * Aplica un factor de ajuste al precio
 */
private function applyPriceFactor($price, $factor)
{
    if ($price == 0 || !$factor || !isset($factor['value']) || !isset($factor['type'])) {
        return $price;
    }
    
    $value = $factor['value'];
    $type = $factor['type'];
    
    switch ($type) {
        case 'amount':
            // Sumar o restar cantidad fija
            $adjustedPrice = $price + $value;
            break;
            
        case 'percentage':
            // Sumar o restar porcentaje
            $adjustedPrice = $price + ($price * ($value / 100));
            break;
            
        default:
            return $price;
    }
    
    // Asegurar que el precio no sea negativo
    if ($adjustedPrice < 0) {
        $adjustedPrice = 0;
    }
    
    // Aplicar formato terminado en 9 solo si no es 0
    if ($adjustedPrice > 0) {
        return $this->formatPriceEndingIn9($adjustedPrice);
    }
    
    return 0;
}
private function formatPriceEndingIn9($price)
{
    if ($price == 0) {
        return 0;
    }
    
    // Convertir a entero
    $intPrice = (int) round($price);
    
    // Si ya termina en 9, devolverlo tal como está
    if ($intPrice % 10 == 9) {
        return $intPrice;
    }
    
    // Obtener el último dígito
    $lastDigit = $intPrice % 10;
    
    // Si el último dígito es menor que 9, sumar para llegar a 9
    if ($lastDigit < 9) {
        return $intPrice + (9 - $lastDigit);
    } else {
        // Si es mayor que 9 (no debería pasar, pero por seguridad)
        // Sumar 10 - lastDigit + 9 para llegar al siguiente 9
        return $intPrice + (10 - $lastDigit) + 9;
    }
}
    
    
    protected $jsonable = ['parameters', 'pricing','override','cards','whmcs_plans'];

    /**
     * Set pricing attribute - ensure it's always an array
     */
    public function setPricingAttribute($value)
    {
        if (is_null($value) || $value === '' || $value === '[]') {
            $this->attributes['pricing'] = json_encode([]);
        } elseif (is_array($value)) {
            $this->attributes['pricing'] = json_encode($value);
        } elseif (is_string($value)) {
            // Try to decode if it's already JSON
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->attributes['pricing'] = $value;
            } else {
                $this->attributes['pricing'] = json_encode([]);
            }
        } else {
            $this->attributes['pricing'] = json_encode([]);
        }
    }
    
    
    public $belongsTo = [
        'service' => 'Aero\Manager\Models\Services'
    ];
    
    public function scopePublic($query)
    {
        return $query->where('public', 1);
    }
    
    public function scopeDomain($query)
    {
        return $query->where('aero_manager_plans.domain',$_SERVER['HTTP_HOST'])->where('aero_manager_plans.public', 1);
    }  
    
    public function scopeBlackFriday($query)
    {
        return $query->where('promo_black_friday_on', 1);
    } 
    
    public $belongsToMany =[
        
        'services' => [
            
            'Aero\Manager\Models\Services',
            'table'     => 'aero_manager_services_plans',
            'name'  => 'name',

        
        ]
   
    ];
    
    public function scopeLite($query)
    {
        $host = $_SERVER['HTTP_HOST'];
    
        return $query->where('domain', $host)
                     ->with(['services' => function($q) use ($host) {
                         $q->where('domain', $host)
                           ->select('id', 'name'); // Los campos que quieres traer
                     }]);
    }

    
}        