<?php namespace Arnold\School\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableDeleteArnoldSchool extends Migration
{
    public function up()
    {
        Schema::dropIfExists('arnold_school_');
    }
    
    public function down()
    {
        Schema::create('arnold_school_', function($table)
        {
            $table->increments('id')->unsigned();
            $table->string('name', 255);
            $table->string('phone', 255);
            $table->integer('mail');
            $table->date('lastname');
            $table->string('birthday', 255);
            $table->integer('ci');
            $table->string('address', 255);
        });
    }
}
