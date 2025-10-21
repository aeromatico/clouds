<?php namespace Arnold\School\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateArnoldSchoolCourses3 extends Migration
{
    public function up()
    {
        Schema::table('arnold_school_courses', function($table)
        {
            $table->smallInteger('student_id');
        });
    }
    
    public function down()
    {
        Schema::table('arnold_school_courses', function($table)
        {
            $table->dropColumn('student_id');
        });
    }
}
