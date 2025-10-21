<?php namespace Aero\Manager\Models;

use Model;

/**
 * Model
 */
class Portal extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'aero_manager_portal';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
    
    public function beforeCreate(){
        $this->domain = str_replace('wwww.','',$_SERVER['HTTP_HOST']);
    }       
    
    protected $jsonable = ['features_special', 'features', 'faqs', 'payment_gateways','promos','customers_project','actionboxes','features_sections','announcements','fonts'];

    public $attachOne = [
        'header_img' => 'System\Models\File',
        'header_bg' => 'System\Models\File',
        'header_video' => 'System\Models\File',
    ];
    
    public $attachMany = [
        'header_imgs' => 'System\Models\File',
        'services_imgs' => 'System\Models\File',
        'customers_logos' => 'System\Models\File',
        'gallery' => 'System\Models\File',
    ];
    
    public function scopeDomain($query)
    {
        return $query->where('domain',$_SERVER['HTTP_HOST']);
    }     
    
     public $belongsToMany =[
        
        'features' => [
            
            'Aero\Manager\Models\Features',
            'table'     => 'aero_manager_portal_features',
            'name'  => 'name'
        
        ],
    
    ];

}