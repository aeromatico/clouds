<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerStoreItems3 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_store_items', function($table)
        {
            $table->string('name', 191)->nullable()->change();
            $table->string('slug', 191)->nullable()->change();
            $table->smallInteger('user_id')->nullable()->default(1)->change();
            $table->string('domain', 191)->nullable()->change();
            $table->text('description_short')->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->text('variants')->nullable()->change();
            $table->text('pricing')->nullable()->change();
            $table->boolean('public_on')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_store_items', function($table)
        {
            $table->string('name', 191)->nullable(false)->change();
            $table->string('slug', 191)->nullable(false)->change();
            $table->smallInteger('user_id')->nullable(false)->default(null)->change();
            $table->string('domain', 191)->nullable(false)->change();
            $table->text('description_short')->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
            $table->text('variants')->nullable(false)->change();
            $table->text('pricing')->nullable(false)->change();
            $table->boolean('public_on')->nullable(false)->change();
        });
    }
}
