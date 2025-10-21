<?php namespace Aero\Paises\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroPaisesPaises4 extends Migration
{
    public function up()
    {
        Schema::table('aero_paises_paises', function($table)
        {
            $table->boolean('reconocido');
        });
    }
    
    public function down()
    {
        Schema::table('aero_paises_paises', function($table)
        {
            $table->dropColumn('reconocido');
        });
    }
}
