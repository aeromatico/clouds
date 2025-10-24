<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * AddDomainColumnToAllTables Migration
 *
 * Agrega el campo 'domain' a todas las tablas del plugin para soporte multi-dominio.
 * El campo tiene índice para optimizar queries y valor por defecto 'boliviahost.com'.
 */
class AddDomainColumnToAllTables extends Migration
{
    /**
     * Tablas que necesitan el campo 'domain'
     */
    protected $tables = [
        'aero_clouds_services',
        'aero_clouds_plans',
        'aero_clouds_features',
        'aero_clouds_addons',
        'aero_clouds_faqs',
        'aero_clouds_docs',
        'aero_clouds_domain_providers',
        'aero_clouds_domain_extensions',
        'aero_clouds_domains',
        'aero_clouds_payment_gateways',
        'aero_clouds_orders',
        'aero_clouds_invoices',
        'aero_clouds_clouds_servers',
        'aero_clouds_tickets',
        'aero_clouds_ticket_replies',
        'aero_clouds_support_departments',
        'aero_clouds_activity_logs',
        'aero_clouds_email_logs'
    ];

    public function up()
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'domain')) {
                Schema::table($table, function (Blueprint $table) {
                    // Agregar columna 'domain' después del 'id'
                    $table->string('domain', 100)
                          ->default('boliviahost.com')
                          ->after('id')
                          ->index()
                          ->comment('Dominio al que pertenece el registro');
                });
            }
        }
    }

    public function down()
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'domain')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('domain');
                });
            }
        }
    }
}
