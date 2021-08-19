<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\User;

class ChangePassword extends Mailable
{
    use Queueable, SerializesModels;
    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $rand_password)
    {
        $this->user = $user;
        $this->data['rand_password'] = $rand_password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {    
        return $this->view('emails.change_password', $this->data);
    }
}
