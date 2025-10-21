<?php namespace Arnold\School\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateArnoldSchoolCourses4 extends Migration
{
    public function up()
    {
        Schema::table('arnold_school_courses', function($table)
        {
            $table->text('webpage');
        });
    }
    
    public function down()
    {
        Schema::table('arnold_school_courses', function($table)
        {
            $table->dropColumn('webpage');
        });
    }
}
