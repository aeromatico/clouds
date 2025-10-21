<?php namespace Arnold\School\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateArnoldSchool2 extends Migration
{
    public function up()
    {
        Schema::create('arnold_school_', function($table)
        {
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('lastname');
            $table->date('birthday');
            $table->integer('ci');
            $table->string('mail');
            $table->integer('phone');
            $table->string('address');
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
