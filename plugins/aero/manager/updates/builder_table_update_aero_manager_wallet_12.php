<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerWallet12 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_wallet', function($table)
        {
            $table->renameColumn('items', 'detail');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_wallet', function($table)
        {
            $table->renameColumn('detail', 'items');
        });
    }
}
