<?php namespace Aero\Clouds\Console;

use Aero\Clouds\Models\DomainProvider;
use Illuminate\Console\Command;
use Exception;

/**
 * SyncDomainPricing Console Command
 *
 * Syncs domain pricing from registrars to the database
 *
 * Usage:
 *   php artisan aero:sync-domain-pricing
 *   php artisan aero:sync-domain-pricing --provider=1
 *   php artisan aero:sync-domain-pricing --provider-type=dynadot
 */
class SyncDomainPricing extends Command
{
    /**
     * @var string The console command name.
     */
    protected $signature = 'aero:sync-domain-pricing
                            {--provider= : Specific provider ID to sync}
                            {--provider-type= : Sync all providers of this type (dynadot, etc)}
                            {--test : Test connection only, do not sync}';

    /**
     * @var string The console command description.
     */
    protected $description = 'Sync domain pricing from registrars';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Starting domain pricing sync...');
        $this->newLine();

        try {
            $providers = $this->getProvidersToSync();

            if ($providers->isEmpty()) {
                $this->error('No providers found to sync.');
                return 1;
            }

            $this->info("Found {$providers->count()} provider(s) to sync.");
            $this->newLine();

            foreach ($providers as $provider) {
                $this->syncProvider($provider);
            }

            $this->newLine();
            $this->info('✓ Domain pricing sync completed!');

            return 0;
        } catch (Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * Get providers to sync based on options
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getProvidersToSync()
    {
        $query = DomainProvider::where('is_active', true);

        // Specific provider ID
        if ($providerId = $this->option('provider')) {
            return $query->where('id', $providerId)->get();
        }

        // Specific provider type
        if ($providerType = $this->option('provider-type')) {
            return $query->where('provider_type', $providerType)->get();
        }

        // All active providers
        return $query->get();
    }

    /**
     * Sync a single provider
     *
     * @param DomainProvider $provider
     */
    protected function syncProvider(DomainProvider $provider): void
    {
        $this->line("Processing: <info>{$provider->name}</info> ({$provider->provider_type})");

        try {
            $registrar = $this->getRegistrar($provider);

            if (!$registrar) {
                $this->warn("  ⚠ Skipping: No registrar adapter available for {$provider->provider_type}");
                return;
            }

            // Test mode
            if ($this->option('test')) {
                $this->testConnection($registrar, $provider);
                return;
            }

            // Sync pricing
            $this->line('  Syncing pricing data...');
            $result = $registrar->syncPricing();

            if ($result['success']) {
                $this->info("  ✓ Synced {$result['synced']} extension(s)");

                if (!empty($result['errors'])) {
                    $errorCount = count($result['errors']);
                    $this->warn("  ⚠ Encountered {$errorCount} error(s):");
                    foreach ($result['errors'] as $error) {
                        $this->line("    - {$error}");
                    }
                }
            } else {
                $this->error("  ✗ Sync failed: " . ($registrar->getLastError() ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            $this->error("  ✗ Error: {$e->getMessage()}");
        }

        $this->newLine();
    }

    /**
     * Test registrar connection
     *
     * @param object $registrar
     * @param DomainProvider $provider
     */
    protected function testConnection($registrar, DomainProvider $provider): void
    {
        $this->line('  Testing connection...');

        if ($registrar->testConnection()) {
            $this->info("  ✓ Connection successful");
        } else {
            $this->error("  ✗ Connection failed: " . ($registrar->getLastError() ?? 'Unknown error'));
        }
    }

    /**
     * Get registrar adapter for provider
     *
     * @param DomainProvider $provider
     * @return object|null
     */
    protected function getRegistrar(DomainProvider $provider): ?object
    {
        switch ($provider->provider_type) {
            case 'dynadot':
                return new \Aero\Clouds\Classes\Registrars\DynadotRegistrar($provider);

            // Add more registrars here in the future
            // case 'namecheap':
            //     return new \Aero\Clouds\Classes\Registrars\NamecheapRegistrar($provider);

            default:
                return null;
        }
    }
}
