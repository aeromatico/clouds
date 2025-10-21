<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerChats extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_chats', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->smallInteger('user_id_from')->nullable()->default(0);
            $table->smallInteger('user_id_to')->nullable()->default(0);
            $table->smallInteger('user_backend_id')->nullable()->default(1);
            $table->boolean('chatbot_on')->nullable()->default(0);
            $table->text('message')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_chats');
    }
}
