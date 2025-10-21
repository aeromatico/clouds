<?php namespace Aero\Ligas\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroLigasClubes extends Migration
{
    public function up()
    {
        Schema::table('aero_ligas_clubes', function($table)
        {
            $table->string('color');
            $table->boolean('descenso');
        });
    }
    
    public function down()
    {
        Schema::table('aero_ligas_clubes', function($table)
        {
            $table->dropColumn('color');
            $table->dropColumn('descenso');
        });
    }
}
