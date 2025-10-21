<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerWallet7 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_wallet', function($table)
        {
            $table->string('whmc_invoice', 191)->nullable()->change();
            $table->string('status', 191)->nullable()->change();
            $table->string('sku', 191)->nullable()->change();
            $table->text('description')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_wallet', function($table)
        {
            $table->string('whmc_invoice', 191)->nullable(false)->change();
            $table->string('status', 191)->nullable(false)->change();
            $table->string('sku', 191)->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
        });
    }
}
