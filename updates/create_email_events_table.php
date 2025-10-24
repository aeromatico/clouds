<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateEmailEventsTable extends Migration
{
    public function up()
    {
        Schema::create('aero_clouds_email_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_code')->unique()->comment('Código único del evento');
            $table->string('event_name')->comment('Nombre descriptivo del evento');
            $table->string('event_category', 50)->default('general')->comment('Categoría: orders, invoices, clouds, domains, support, tasks, users');
            $table->text('description')->nullable()->comment('Descripción del evento');

            // Templates configuration
            $table->string('user_template_code')->nullable()->comment('Plantilla para usuarios finales');
            $table->string('admin_template_code')->nullable()->comment('Plantilla para administradores');

            // Recipient configuration
            $table->boolean('notify_user')->default(false)->comment('Enviar notificación al usuario involucrado');
            $table->boolean('notify_admin')->default(false)->comment('Enviar notificación a administradores');

            // Status
            $table->boolean('enabled')->default(true)->comment('Evento activo/inactivo');
            $table->integer('priority')->default(0)->comment('Prioridad de procesamiento (mayor = más importante)');

            // Additional context
            $table->json('context_vars')->nullable()->comment('Variables de contexto disponibles para las plantillas');

            $table->string('domain')->index()->default('clouds.com.bo');
            $table->timestamps();

            $table->index(['event_category', 'enabled']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_clouds_email_events');
    }
}
