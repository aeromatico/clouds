<?php namespace Aero\Ligas\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroLigasClubes extends Migration
{
    public function up()
    {
        Schema::create('aero_ligas_clubes', function($table)
        {
            $table->increments('id')->unsigned();
            $table->string('nombre');
            $table->string('estadio');
            $table->string('liga');
            $table->string('pais');
            $table->string('trofeos');
            $table->date('fundacion');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_ligas_clubes');
    }
}
