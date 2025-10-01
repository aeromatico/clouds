<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Aero\Clouds\Models\Service;
use Illuminate\Support\Str;

class AddFieldsToServicesTable extends Migration
{
    public function up()
    {
        // Add columns if they don't exist
        if (!Schema::hasColumn('aero_clouds_services', 'slug')) {
            Schema::table('aero_clouds_services', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });
        }

        if (!Schema::hasColumn('aero_clouds_services', 'short_description')) {
            Schema::table('aero_clouds_services', function (Blueprint $table) {
                $table->string('short_description')->nullable()->after('slug');
            });
        }

        if (!Schema::hasColumn('aero_clouds_services', 'menu_description')) {
            Schema::table('aero_clouds_services', function (Blueprint $table) {
                $table->text('menu_description')->nullable()->after('short_description');
            });
        }

        if (!Schema::hasColumn('aero_clouds_services', 'html_description')) {
            Schema::table('aero_clouds_services', function (Blueprint $table) {
                $table->longText('html_description')->nullable()->after('menu_description');
            });
        }

        if (!Schema::hasColumn('aero_clouds_services', 'icon')) {
            Schema::table('aero_clouds_services', function (Blueprint $table) {
                $table->string('icon')->nullable()->after('html_description');
            });
        }

        // Generate slugs for existing services
        $services = Service::all();
        foreach ($services as $service) {
            if (empty($service->slug)) {
                $slug = Str::slug($service->name);
                $originalSlug = $slug;
                $counter = 1;

                while (Service::where('slug', $slug)->where('id', '!=', $service->id)->exists()) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }

                $service->slug = $slug;
                $service->save();
            }
        }

        // Add unique constraint if it doesn't exist
        try {
            Schema::table('aero_clouds_services', function (Blueprint $table) {
                $table->unique('slug');
            });
        } catch (\Exception $e) {
            // Index already exists, skip
        }
    }

    public function down()
    {
        Schema::table('aero_clouds_services', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'short_description',
                'menu_description',
                'html_description',
                'icon'
            ]);
        });
    }
}