<?php namespace Arnold\School\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateArnoldSchoolStudents2 extends Migration
{
    public function up()
    {
        Schema::table('arnold_school_students', function($table)
        {
            $table->smallInteger('courses_id');
        });
    }
    
    public function down()
    {
        Schema::table('arnold_school_students', function($table)
        {
            $table->dropColumn('courses_id');
        });
    }
}
