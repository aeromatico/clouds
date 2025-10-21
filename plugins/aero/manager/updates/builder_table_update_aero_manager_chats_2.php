<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerChats2 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_chats', function($table)
        {
            $table->smallInteger('user_id')->nullable()->default(0);
            $table->boolean('favorite_on');
            $table->dropColumn('user_id_from');
            $table->dropColumn('user_id_to');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_chats', function($table)
        {
            $table->dropColumn('user_id');
            $table->dropColumn('favorite_on');
            $table->smallInteger('user_id_from')->nullable()->default(0);
            $table->smallInteger('user_id_to')->nullable()->default(0);
        });
    }
}
