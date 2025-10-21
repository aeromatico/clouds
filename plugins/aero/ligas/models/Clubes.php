<?php namespace Aero\Ligas\Models;

use Model;

/**
 * Model
 */
class Clubes extends Model
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
    public $table = 'aero_ligas_clubes';

    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];
  
  public $attachOne = [
    'escudo' => \System\Models\File::class
];

public $attachMany = [
    'fotos' => \System\Models\File::class
];

}
