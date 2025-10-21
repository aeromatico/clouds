<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerChats4 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_chats', function($table)
        {
            $table->renameColumn('user_id', 'user_id_from');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_chats', function($table)
        {
            $table->renameColumn('user_id_from', 'user_id');
        });
    }
}
