<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends Notification
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Reestablece tu contraseña - BikeControl')
            ->greeting('¡Hola!')
            ->line('Recibiste este correo porque se solicitó un reinicio de contraseña para tu cuenta en BikeControl.')
            ->action('Reestablecer Contraseña', $url)
            ->line('Si tú no realizaste esta solicitud, puedes ignorar este correo. Tu contraseña no cambiará hasta que accedas al enlace y crees una nueva.');
    }
}
