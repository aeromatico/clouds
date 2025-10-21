<?php namespace Arnold\School\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateArnoldSchoolStudents4 extends Migration
{
    public function up()
    {
        Schema::table('arnold_school_students', function($table)
        {
            $table->text('social');
        });
    }
    
    public function down()
    {
        Schema::table('arnold_school_students', function($table)
        {
            $table->dropColumn('social');
        });
    }
}
