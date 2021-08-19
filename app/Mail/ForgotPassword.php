<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\User;

class ForgotPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $reset_code)
    {
        $this->user = $user;
        $this->data['reset_code'] = $reset_code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {    
        return $this->view('emails.forgot_password', $this->data);
    }
}
