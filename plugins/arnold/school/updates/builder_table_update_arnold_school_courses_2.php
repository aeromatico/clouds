<?php namespace Arnold\School\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateArnoldSchoolCourses2 extends Migration
{
    public function up()
    {
        Schema::table('arnold_school_courses', function($table)
        {
            $table->dropColumn('students_id');
        });
    }
    
    public function down()
    {
        Schema::table('arnold_school_courses', function($table)
        {
            $table->smallInteger('students_id');
        });
    }
}
