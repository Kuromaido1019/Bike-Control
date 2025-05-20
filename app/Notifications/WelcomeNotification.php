<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WelcomeNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('¡Bienvenido a BikeControl!')
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Te damos la bienvenida a BikeControl. Ya puedes acceder al sistema con tus credenciales.')
            ->line('Si tienes dudas o necesitas ayuda, contáctanos. ¡Gracias por registrarte!')
            ->salutation('Saludos, el equipo de BikeControl');
    }
}
