<?php namespace Aero\Clouds\Console;

use Illuminate\Console\Command;
use Schema;
use DB;

class AddDomainColumns extends Command
{
    protected $signature = 'aero:add-domain-columns';
    protected $description = 'Add domain column to tables that are missing it';

    public function handle()
    {
        $this->info('Adding domain columns to tables...');

        $tables = [
            'aero_clouds_plans',
            'aero_clouds_services',
            'aero_clouds_features',
            'aero_clouds_faqs',
            'aero_clouds_docs',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                $this->warn("  - Table {$table} does not exist, skipping");
                continue;
            }

            if (Schema::hasColumn($table, 'domain')) {
                $this->warn("  - {$table} already has domain column");
                continue;
            }

            try {
                Schema::table($table, function ($table) {
                    $table->string('domain')->default('clouds.com.bo')->index()->after('id');
                });

                // Update existing records to have the default domain
                DB::table($table)->whereNull('domain')->update(['domain' => 'clouds.com.bo']);

                $this->info("  ✓ Added domain column to {$table}");
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to add domain to {$table}: " . $e->getMessage());
            }
        }

        $this->info('✓ Domain columns added successfully!');
    }
}
