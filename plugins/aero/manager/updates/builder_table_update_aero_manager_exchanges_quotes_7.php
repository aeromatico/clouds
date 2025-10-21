<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerExchangesQuotes7 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_exchanges_quotes', function($table)
        {
            $table->decimal('fee', 10, 0)->nullable()->change();
            $table->text('fee_ext_detail')->nullable()->change();
            $table->text('advice')->nullable()->change();
            $table->text('observations')->nullable()->change();
            $table->text('chat')->nullable()->change();
            $table->dateTime('endtime')->nullable()->change();
            $table->string('status', 191)->nullable()->change();
            $table->smallInteger('user_id')->nullable()->change();
            $table->smallInteger('backend_user_id')->nullable()->change();
            $table->string('identifier', 191)->nullable()->change();
            $table->smallInteger('from_id')->nullable()->change();
            $table->smallInteger('to_id')->nullable()->change();
            $table->smallInteger('fee_ext')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_exchanges_quotes', function($table)
        {
            $table->decimal('fee', 10, 0)->nullable(false)->change();
            $table->text('fee_ext_detail')->nullable(false)->change();
            $table->text('advice')->nullable(false)->change();
            $table->text('observations')->nullable(false)->change();
            $table->text('chat')->nullable(false)->change();
            $table->dateTime('endtime')->nullable(false)->change();
            $table->string('status', 191)->nullable(false)->change();
            $table->smallInteger('user_id')->nullable(false)->change();
            $table->smallInteger('backend_user_id')->nullable(false)->change();
            $table->string('identifier', 191)->nullable(false)->change();
            $table->smallInteger('from_id')->nullable(false)->change();
            $table->smallInteger('to_id')->nullable(false)->change();
            $table->smallInteger('fee_ext')->nullable(false)->change();
        });
    }
}
