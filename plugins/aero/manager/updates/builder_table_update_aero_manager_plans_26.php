<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPlans26 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->dateTime('promo_date_end');
            $table->smallInteger('whmcs_plan_id')->nullable(false)->default(null)->change();
            $table->renameColumn('promo', 'promo_on');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->dropColumn('promo_date_end');
            $table->smallInteger('whmcs_plan_id')->nullable()->default(NULL)->change();
            $table->renameColumn('promo_on', 'promo');
            $table->timestamp('created_at')->nullable()->default('NULL');
            $table->timestamp('updated_at')->nullable()->default('NULL');
            $table->timestamp('deleted_at')->nullable()->default('NULL');
        });
    }
}
