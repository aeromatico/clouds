<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPlans24 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->boolean('promo');
            $table->boolean('popular')->nullable(false)->unsigned(false)->default(null)->change();
            $table->smallInteger('whmcs_plan_id')->default(null)->change();
            $table->string('sites_number', 191)->default('1')->change();
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->dropColumn('promo');
            $table->smallInteger('popular')->nullable(false)->unsigned(false)->default(null)->change();
            $table->smallInteger('whmcs_plan_id')->default(NULL)->change();
            $table->string('sites_number', 191)->default('NULL')->change();
            $table->timestamp('created_at')->nullable()->default('NULL');
            $table->timestamp('updated_at')->nullable()->default('NULL');
            $table->timestamp('deleted_at')->nullable()->default('NULL');
        });
    }
}
