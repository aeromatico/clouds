<?php namespace Arnold\School\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateArnoldSchoolStudents3 extends Migration
{
    public function up()
    {
        Schema::table('arnold_school_students', function($table)
        {
            $table->renameColumn('courses_id', 'course_id');
        });
    }
    
    public function down()
    {
        Schema::table('arnold_school_students', function($table)
        {
            $table->renameColumn('course_id', 'courses_id');
        });
    }
}
