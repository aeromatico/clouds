<?php namespace Aero\ApiHub\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddSourceToApis extends Migration
{
    public function up()
    {
        Schema::table('aero_apihub_apis', function ($table) {
            $table->enum('source', ['apis_guru', 'apify', 'manual', 'legacy'])
                ->default('manual')
                ->after('category');
        });
    }

    public function down()
    {
        Schema::table('aero_apihub_apis', function ($table) {
            $table->dropColumn('source');
        });
    }
}
