<?php namespace Aero\Paises\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroPaisesPaises2 extends Migration
{
    public function up()
    {
        Schema::table('aero_paises_paises', function($table)
        {
            $table->renameColumn('ideologia', 'idioma');
        });
    }
    
    public function down()
    {
        Schema::table('aero_paises_paises', function($table)
        {
            $table->renameColumn('idioma', 'ideologia');
        });
    }
}
