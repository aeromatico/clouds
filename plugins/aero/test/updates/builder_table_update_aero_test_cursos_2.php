<?php namespace Aero\Test\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroTestCursos2 extends Migration
{
    public function up()
    {
        Schema::table('aero_test_cursos', function($table)
        {
            $table->date('curse_created')->nullable()->unsigned(false)->default(null)->comment(null)->change();
            $table->date('curse_updated')->nullable()->unsigned(false)->default(null)->comment(null)->change();
            $table->date('curse_deleted')->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_test_cursos', function($table)
        {
            $table->dateTime('curse_created')->nullable()->unsigned(false)->default(null)->comment(null)->change();
            $table->dateTime('curse_updated')->nullable()->unsigned(false)->default(null)->comment(null)->change();
            $table->dateTime('curse_deleted')->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
}
