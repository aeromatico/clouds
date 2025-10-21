<?php namespace Aero\Test\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroTestStudents extends Migration
{
    public function up()
    {
        Schema::create('aero_test_students', function($table)
        {
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->date('birthday');
            $table->string('address');
            $table->string('mobile');
            $table->string('email');
            $table->text('bio');
            $table->boolean('active');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_test_students');
    }
}
