<?php namespace Aero\Manager\Models;

use Model;

/**
 * Model
 */
class Settings extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'aero_manager_settings';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
    
    public function beforeCreate(){
        $this->domain = str_replace('wwww.','',$_SERVER['HTTP_HOST']);
    }    
    
    public $attachOne = [
        'logo' => 'System\Models\File',
        'logo_dark' => 'System\Models\File',
        'favicon' => 'System\Models\File',
    ];
    
    protected $jsonable = ['menus','metas','sitemap'];
    
  
    
    public $attachMany = [
        'gallery' => 'System\Models\File',
    ];
    
}
