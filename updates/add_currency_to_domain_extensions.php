<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddCurrencyToDomainExtensions extends Migration
{
    public function up()
    {
        Schema::table('aero_clouds_domain_extensions', function($table)
        {
            $table->string('currency', 3)->default('USD')->after('redemption_price');
        });
    }

    public function down()
    {
        Schema::table('aero_clouds_domain_extensions', function($table)
        {
            $table->dropColumn('currency');
        });
    }
}
