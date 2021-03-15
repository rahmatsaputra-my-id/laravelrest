<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class verifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $address = 'noreply.verifyotp@gmail.com';
        $subjectVar = 'Validation your Account';
        $name = 'Google';

        return $this->view('emails.test')
                    ->from($address, $name)
                    ->subject($subjectVar)
                    ->with([ 'test_message' => $this->data['message']]);
    }
}
