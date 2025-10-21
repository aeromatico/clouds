<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerWallet10 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_wallet', function($table)
        {
            $table->text('items')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_wallet', function($table)
        {
            $table->text('items')->nullable(false)->change();
        });
    }
}
