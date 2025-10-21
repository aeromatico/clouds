<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerChats7 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_chats', function($table)
        {
            $table->string('type')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_chats', function($table)
        {
            $table->dropColumn('type');
        });
    }
}
