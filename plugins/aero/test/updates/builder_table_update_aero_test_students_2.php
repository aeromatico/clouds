<?php namespace Aero\Test\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroTestStudents2 extends Migration
{
    public function up()
    {
        Schema::table('aero_test_students', function($table)
        {
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('aero_test_students', function($table)
        {
            $table->dropColumn('deleted_at');
        });
    }
}
