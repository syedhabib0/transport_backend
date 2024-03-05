<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DriverImportNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

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

    public function build()
    {
        return $this->subject('Driver Registration')->markdown('emails.driver-registration-email', ['user_data' => $this->userData, 'password' => $this->password]);
    }
}
