<?php namespace Aero\Clouds\Console;

use Illuminate\Console\Command;
use RainLab\User\Models\User;
use Mail;
use Log;

/**
 * TestEmailVerification Command
 *
 * Comando para probar el envío de correos de verificación
 */
class TestEmailVerification extends Command
{
    /**
     * @var string signature
     */
    protected $signature = 'aero:test-email-verification {email?} {--create}';

    /**
     * @var string description
     */
    protected $description = 'Prueba el envío de correos de verificación de email';

    /**
     * handle
     */
    public function handle()
    {
        $email = $this->argument('email');
        $createUser = $this->option('create');

        // Si no se proporciona email, preguntar
        if (!$email) {
            $email = $this->ask('¿A qué email quieres enviar la prueba?');
        }

        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('❌ Email inválido: ' . $email);
            return 1;
        }

        $this->info('🔍 Buscando usuario con email: ' . $email);

        // Buscar o crear usuario
        $user = User::where('email', $email)->first();

        if (!$user && $createUser) {
            $this->info('👤 Creando usuario de prueba...');

            $user = User::create([
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => $email,
                'password' => 'Test1234',
                'password_confirmation' => 'Test1234',
            ]);

            $this->info('✅ Usuario creado con ID: ' . $user->id);
        } elseif (!$user) {
            $this->error('❌ Usuario no encontrado. Usa --create para crear uno nuevo.');
            $this->line('');
            $this->line('Ejemplo: php artisan aero:test-email-verification ' . $email . ' --create');
            return 1;
        } else {
            $this->info('✅ Usuario encontrado: ' . $user->full_name . ' (ID: ' . $user->id . ')');
        }

        // Mostrar estado de verificación
        if ($user->hasVerifiedEmail()) {
            $this->warn('⚠️  El usuario ya tiene el email verificado');
            $this->warn('   Verificado en: ' . $user->activated_at->format('Y-m-d H:i:s'));

            if (!$this->confirm('¿Deseas enviar el correo de verificación de todas formas?', true)) {
                return 0;
            }
        } else {
            $this->info('ℹ️  El usuario NO tiene el email verificado');
        }

        $this->line('');
        $this->info('📧 Preparando envío de correo de verificación...');

        try {
            // Establecer URL de verificación
            $user->setUrlForEmailVerification(\Cms::url('/verify-email'));

            // Enviar correo de verificación
            $user->sendEmailVerificationNotification();

            $this->line('');
            $this->info('✅ ¡Correo de verificación enviado exitosamente!');
            $this->line('');

            // Mostrar detalles
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['Destinatario', $user->email],
                    ['Nombre', $user->full_name],
                    ['Template', 'user:verify_email'],
                    ['URL Base', \Cms::url('/verify-email')],
                    ['Código generado', '✓'],
                ]
            );

            // Instrucciones
            $this->line('');
            $this->info('📋 Próximos pasos:');
            $this->line('1. Revisa la bandeja de entrada de: ' . $email);
            $this->line('2. Busca el email con asunto: "Confirm your email address"');
            $this->line('3. Haz clic en el botón "Confirm Email Address"');
            $this->line('');

            // Ver logs
            $this->info('📊 Para ver logs:');
            $this->line('   tail -f storage/logs/system.log');
            $this->line('');

            // Ver en backend
            $this->info('🖥️  Ver en backend:');
            $this->line('   Cloud Commerce → Email Logs');
            $this->line('');

            Log::info('Email verification test sent', [
                'email' => $email,
                'user_id' => $user->id,
                'command' => 'aero:test-email-verification'
            ]);

            // Log in email logs table
            try {
                \Aero\Clouds\Models\EmailLog::create([
                    'template_code' => 'user:verify_email',
                    'recipient_email' => $user->email,
                    'recipient_name' => $user->full_name,
                    'user_id' => $user->id,
                    'data' => json_encode([
                        'first_name' => $user->first_name,
                        'email' => $user->email,
                        'test_mode' => true,
                    ]),
                    'metadata' => json_encode([
                        'source' => 'test_command',
                        'command' => 'aero:test-email-verification',
                    ]),
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);

                $this->line('✅ Registrado en Email Logs (ID guardado)');
            } catch (\Exception $e) {
                $this->warn('⚠️  No se pudo guardar en Email Logs: ' . $e->getMessage());
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error al enviar correo: ' . $e->getMessage());
            $this->line('');
            $this->error('Stack trace:');
            $this->line($e->getTraceAsString());

            Log::error('Email verification test failed', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return 1;
        }
    }
}
