<?php namespace Aero\Paises\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroPaisesPaises3 extends Migration
{
    public function up()
    {
        Schema::table('aero_paises_paises', function($table)
        {
            $table->date('creacion')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('aero_paises_paises', function($table)
        {
            $table->dropColumn('creacion');
        });
    }
}
