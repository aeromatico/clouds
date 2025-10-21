<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerChats3 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_chats', function($table)
        {
            $table->text('room')->nullable()->change();
            $table->boolean('favorite_on')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_chats', function($table)
        {
            $table->text('room')->nullable(false)->change();
            $table->boolean('favorite_on')->nullable(false)->change();
        });
    }
}
