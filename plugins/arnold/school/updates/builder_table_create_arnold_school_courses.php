<?php namespace Arnold\School\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateArnoldSchoolCourses extends Migration
{
    public function up()
    {
        Schema::create('arnold_school_courses', function($table)
        {
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('description');
            $table->string('mode');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('active');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('arnold_school_courses');
    }
}
