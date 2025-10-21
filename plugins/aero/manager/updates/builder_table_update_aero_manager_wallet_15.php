<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerWallet15 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_wallet', function($table)
        {
            $table->string('gateway', 191)->nullable()->change();
            $table->smallInteger('fee')->nullable()->change();
            $table->string('domain', 191)->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_wallet', function($table)
        {
            $table->string('gateway', 191)->nullable(false)->change();
            $table->smallInteger('fee')->nullable(false)->change();
            $table->string('domain', 191)->nullable(false)->change();
        });
    }
}
