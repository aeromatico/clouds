<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddSlugToPlansTable extends Migration
{
    public function up()
    {
        // Check if slug column doesn't exist, then add it
        if (!Schema::hasColumn('aero_clouds_plans', 'slug')) {
            Schema::table('aero_clouds_plans', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });
        }

        // Generate slugs for existing plans
        $plans = \Aero\Clouds\Models\Plan::all();
        foreach ($plans as $plan) {
            if (empty($plan->slug)) {
                $plan->slug = $plan->generateSlug($plan->name);
                $plan->save();
            }
        }

        // Now add the unique constraint if it doesn't exist
        $indexes = \DB::select("SHOW INDEX FROM aero_clouds_plans WHERE Key_name = 'aero_clouds_plans_slug_unique'");
        if (empty($indexes)) {
            Schema::table('aero_clouds_plans', function (Blueprint $table) {
                $table->string('slug')->nullable(false)->unique()->change();
            });
        }
    }

    public function down()
    {
        Schema::table('aero_clouds_plans', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
}