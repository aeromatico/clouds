<?php namespace Aero\Clouds\Console;

use Illuminate\Console\Command;
use RainLab\User\Models\User;
use Event;
use Log;

/**
 * TestRegistration Command
 *
 * Simula el registro completo de un usuario para probar el envío de emails
 */
class TestRegistration extends Command
{
    /**
     * @var string signature
     */
    protected $signature = 'aero:test-registration {email?}';

    /**
     * @var string description
     */
    protected $description = 'Simula el registro completo de un usuario (incluyendo evento y email)';

    /**
     * handle
     */
    public function handle()
    {
        $email = $this->argument('email');

        if (!$email) {
            $email = $this->ask('¿Qué email quieres usar para la prueba?');
        }

        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('❌ Email inválido: ' . $email);
            return 1;
        }

        // Verificar si el usuario ya existe
        if (User::where('email', $email)->exists()) {
            $this->error('❌ El usuario con email ' . $email . ' ya existe');
            $this->line('');
            $this->line('Usa un email diferente o elimina el usuario existente primero');
            return 1;
        }

        $this->info('🔄 Simulando registro completo de usuario...');
        $this->line('');

        try {
            // Crear el usuario (simulando el proceso de Registration::onRegister)
            $this->info('👤 Creando usuario...');

            $user = User::create([
                'first_name' => 'Test',
                'last_name' => 'Registration',
                'email' => $email,
                'password' => 'Test1234',
                'password_confirmation' => 'Test1234',
            ]);

            $this->info('✅ Usuario creado con ID: ' . $user->id);
            $this->line('');

            // Disparar el evento (simulando lo que hace Registration::onRegister)
            $this->info('🔔 Disparando evento rainlab.user.register...');

            Event::fire('rainlab.user.register', [$user]);

            $this->line('');
            $this->info('✅ Evento disparado');
            $this->line('');

            // Esperar un momento para que se procesen los eventos
            sleep(1);

            // Mostrar resultados
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['ID', $user->id],
                    ['Email', $user->email],
                    ['Nombre', $user->full_name],
                    ['Verificado', $user->hasVerifiedEmail() ? 'Sí' : 'No'],
                    ['Creado', $user->created_at->format('Y-m-d H:i:s')],
                ]
            );

            $this->line('');
            $this->info('📧 Verificando envío de email...');
            $this->line('');

            // Verificar en logs
            $emailLog = \Aero\Clouds\Models\EmailLog::where('recipient_email', $email)
                ->where('template_code', 'user:verify_email')
                ->latest()
                ->first();

            if ($emailLog) {
                $this->info('✅ Email de verificación registrado en base de datos');
                $this->table(
                    ['Campo', 'Valor'],
                    [
                        ['ID Log', $emailLog->id],
                        ['Template', $emailLog->template_code],
                        ['Estado', $emailLog->status],
                        ['Enviado', $emailLog->sent_at ? $emailLog->sent_at->format('Y-m-d H:i:s') : 'N/A'],
                    ]
                );
            } else {
                $this->warn('⚠️  No se encontró registro de email en la base de datos');
                $this->line('');
                $this->line('Revisa los logs del sistema para más información');
            }

            $this->line('');
            $this->info('📊 Verificar logs del sistema:');
            $this->line('   tail -f storage/logs/system.log | grep "' . $email . '"');
            $this->line('');

            $this->info('🖥️  Ver en backend:');
            $this->line('   Backend → Users → Manage Users');
            $this->line('   Cloud Commerce → Email Logs');
            $this->line('');

            $this->info('🎉 ¡Registro simulado completado!');

            Log::info('Test registration completed', [
                'email' => $email,
                'user_id' => $user->id,
                'email_log_exists' => $emailLog ? true : false,
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error durante el registro: ' . $e->getMessage());
            $this->line('');
            $this->error('Stack trace:');
            $this->line($e->getTraceAsString());

            Log::error('Test registration failed', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        }
    }
}
