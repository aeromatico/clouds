<?php namespace Aero\Test\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroTestStudents3 extends Migration
{
    public function up()
    {
        Schema::table('aero_test_students', function($table)
        {
            $table->smallInteger('curse_id');
        });
    }
    
    public function down()
    {
        Schema::table('aero_test_students', function($table)
        {
            $table->dropColumn('curse_id');
        });
    }
}
