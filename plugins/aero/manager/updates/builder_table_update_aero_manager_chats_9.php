<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerChats9 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_chats', function($table)
        {
            $table->text('prompt')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_chats', function($table)
        {
            $table->dropColumn('prompt');
        });
    }
}
