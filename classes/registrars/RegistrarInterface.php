<?php namespace Aero\Clouds\Classes\Registrars;

/**
 * RegistrarInterface
 * Interface for domain registrar integrations
 */
interface RegistrarInterface
{
    /**
     * Fetch all available TLD extensions from the registrar
     *
     * @return array Array of extension data
     */
    public function fetchExtensions(): array;

    /**
     * Fetch pricing for a specific TLD
     *
     * @param string $tld The TLD to fetch pricing for (e.g., '.com')
     * @return array Pricing data
     */
    public function fetchPricing(string $tld): array;

    /**
     * Sync all pricing data to database
     *
     * @return array ['success' => bool, 'synced' => int, 'errors' => array]
     */
    public function syncPricing(): array;

    /**
     * Test API connection and credentials
     *
     * @return bool True if connection is successful
     */
    public function testConnection(): bool;

    /**
     * Get the last error message
     *
     * @return string|null
     */
    public function getLastError(): ?string;
}
