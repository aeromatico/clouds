<?php namespace Aero\Paises\Models;

use Model;

/**
 * Model
 */
class Paises extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var bool timestamps are disabled.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var string table in the database used by the model.
     */
    public $table = 'aero_paises_paises';

    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];
  
  
  public $attachOne = [
    'bandera' => 'System\Models\File'
];
  public $attachMany = [
    'photos' => 'System\Models\File'
];


}
