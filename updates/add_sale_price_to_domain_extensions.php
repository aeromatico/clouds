<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddSalePriceToDomainExtensions extends Migration
{
    public function up()
    {
        Schema::table('aero_clouds_domain_extensions', function($table)
        {
            $table->decimal('sale_price', 10, 2)->nullable()->after('currency');
        });
    }

    public function down()
    {
        Schema::table('aero_clouds_domain_extensions', function($table)
        {
            $table->dropColumn('sale_price');
        });
    }
}
