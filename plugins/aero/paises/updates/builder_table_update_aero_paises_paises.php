<?php namespace Aero\Paises\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroPaisesPaises extends Migration
{
    public function up()
    {
        Schema::table('aero_paises_paises', function($table)
        {
            $table->increments('id')->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_paises_paises', function($table)
        {
            $table->integer('id')->change();
        });
    }
}
