<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateEmailLogsTable extends Migration
{
    public function up()
    {
        Schema::create('aero_clouds_email_logs', function($table)
        {
            $table->id();
            $table->string('template_code');
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->json('data')->nullable();
            $table->json('metadata')->nullable();
            $table->enum('status', ['sent', 'failed', 'queued'])->default('queued');
            $table->text('error')->nullable();
            $table->float('duration_ms')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->index('template_code');
            $table->index('recipient_email');
            $table->index('user_id');
            $table->index('status');
            $table->index('sent_at');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_clouds_email_logs');
    }
}
