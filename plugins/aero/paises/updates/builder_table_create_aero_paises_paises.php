<?php namespace Aero\Paises\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroPaisesPaises extends Migration
{
    public function up()
    {
        Schema::create('aero_paises_paises', function($table)
        {
            $table->integer('id')->unsigned();
            $table->string('nombre');
            $table->string('capital');
            $table->integer('extension');
            $table->integer('poblacion');
            $table->string('ideologia');
            $table->string('continente');
            $table->primary(['id']);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_paises_paises');
    }
}
