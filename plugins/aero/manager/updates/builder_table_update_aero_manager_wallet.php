<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerWallet extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_wallet', function($table)
        {
            $table->decimal('credits', 10, 0)->nullable();
            $table->decimal('debits', 10, 0)->nullable();
            $table->decimal('load_amount', 10, 0)->nullable();
            $table->string('load_currency')->nullable()->default('BOB');
            $table->smallInteger('user_id')->nullable()->change();
            $table->dropColumn('plan_id');
            $table->dropColumn('credit_load');
            $table->dropColumn('credit_spend');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_wallet', function($table)
        {
            $table->dropColumn('credits');
            $table->dropColumn('debits');
            $table->dropColumn('load_amount');
            $table->dropColumn('load_currency');
            $table->smallInteger('user_id')->nullable(false)->change();
            $table->smallInteger('plan_id');
            $table->boolean('credit_load');
            $table->boolean('credit_spend');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
}
