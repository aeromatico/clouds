<?php namespace Aero\Test\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroTestCursos extends Migration
{
    public function up()
    {
        Schema::table('aero_test_cursos', function($table)
        {
            $table->dateTime('curse_created')->nullable();
            $table->dateTime('curse_updated')->nullable();
            $table->dateTime('curse_deleted')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('aero_test_cursos', function($table)
        {
            $table->dropColumn('curse_created');
            $table->dropColumn('curse_updated');
            $table->dropColumn('curse_deleted');
        });
    }
}
