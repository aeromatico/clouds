<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPlans46 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->string('blackfriday_coupon', 191)->default(null)->change();
            $table->decimal('promo_discount', 10, 0)->nullable(false)->default(0)->change();
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->string('blackfriday_coupon', 191)->default('\'0\'')->change();
            $table->decimal('promo_discount', 10, 0)->nullable()->default(NULL)->change();
            $table->timestamp('created_at')->nullable()->default('NULL');
            $table->timestamp('updated_at')->nullable()->default('NULL');
            $table->timestamp('deleted_at')->nullable()->default('NULL');
        });
    }
}
