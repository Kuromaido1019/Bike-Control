<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;

class SendPasswordResetToUnverifiedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:send-password-reset-unverified';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía correos de restablecimiento de contraseña a usuarios que no han verificado su email (email_verified_at es null)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::whereNull('email_verified_at')->orderBy('id')->first();
        if ($user) {
            try {
                $token = Password::broker()->createToken($user);
                $user->notify(new ResetPasswordNotification($token));
                Log::info('Correo de restablecimiento enviado automáticamente', ['user_id' => $user->id, 'email' => $user->email]);
                $this->info("Correo enviado a: {$user->email}");
            } catch (\Exception $e) {
                Log::error('Error al enviar correo de restablecimiento automático', ['user_id' => $user->id, 'email' => $user->email, 'error' => $e->getMessage()]);
                $this->error("Error con {$user->email}: {$e->getMessage()}");
            }
        } else {
            $this->info('No hay usuarios pendientes de verificación.');
        }
    }
}
