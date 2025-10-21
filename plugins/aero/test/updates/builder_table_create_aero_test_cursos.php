<?php namespace Aero\Test\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroTestCursos extends Migration
{
    public function up()
    {
        Schema::create('aero_test_cursos', function($table)
        {
            $table->increments('id')->unsigned();
            $table->string('name_curse');
            $table->text('description');
            $table->string('mode');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('state');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_test_cursos');
    }
}
