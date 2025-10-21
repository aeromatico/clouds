<?php namespace Arnold\School\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateArnoldSchool extends Migration
{
    public function up()
    {
        Schema::create('arnold_school_', function($table)
        {
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('adress');
            $table->string('turn');
            $table->integer('phone');
            $table->string('mail');
            $table->boolean('active');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('arnold_school_');
    }
}
