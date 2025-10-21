<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPlans40 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->decimal('promo_discount', 10, 0);
            $table->string('promo_coupon');
            $table->dateTime('promo_date_end')->default(null)->change();
            $table->string('blackfriday_coupon', 191)->default('0')->change();
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->dropColumn('promo_discount');
            $table->dropColumn('promo_coupon');
            $table->dateTime('promo_date_end')->default('NULL')->change();
            $table->string('blackfriday_coupon', 191)->default(null)->change();
            $table->timestamp('created_at')->nullable()->default('NULL');
            $table->timestamp('updated_at')->nullable()->default('NULL');
            $table->timestamp('deleted_at')->nullable()->default('NULL');
        });
    }
}
