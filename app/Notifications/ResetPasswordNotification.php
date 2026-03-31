<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $token)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Restablecer Contraseña — Popcorn Compliance')
            ->greeting('Hola, ' . $notifiable->name)
            ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta en Popcorn Compliance.')
            ->action('Restablecer Contraseña', $url)
            ->line('Este enlace expirará en 60 minutos.')
            ->line('Si no solicitaste un cambio de contraseña, puedes ignorar este correo. Tu cuenta está segura.')
            ->salutation('Saludos, **El equipo de Popcorn Compliance**');
    }
}
