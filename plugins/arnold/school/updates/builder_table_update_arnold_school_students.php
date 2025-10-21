<?php namespace Arnold\School\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateArnoldSchoolStudents extends Migration
{
    public function up()
    {
        Schema::rename('arnold_school_', 'arnold_school_students');
    }
    
    public function down()
    {
        Schema::rename('arnold_school_students', 'arnold_school_');
    }
}
