<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DriverImportNotification extends Notification
{
    use Queueable;

    public $userData;
    public $password;

    /**
     * Create a new notification instance.
     */
    public function __construct($userData, $password)
    {
        $this->userData = $userData;
        $this->password = $password;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Driver Registration')
            ->line('Welcome to our platform!')
            ->line('Congratulations! '. $this->userData['first_name'] . ' ' . $this->userData['last_name'] .' You are successfully registered on IWS Transport Service')
            ->line('Here is your password for login (remember to reset your password on first login): ' . $this->password)
            ->action('<strong>Reset Password</strong>', url('/password/reset'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            // Additional data to be sent in the notification array
            'user_data' => $this->userData,
        ];
    }
}
