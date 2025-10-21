<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerWallet11 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_wallet', function($table)
        {
            $table->string('gateway');
            $table->smallInteger('fee');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_wallet', function($table)
        {
            $table->dropColumn('gateway');
            $table->dropColumn('fee');
        });
    }
}
