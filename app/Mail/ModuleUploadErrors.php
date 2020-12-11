<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ModuleUploadErrors extends Mailable
{
    use Queueable, SerializesModels;

    protected $errors;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($errors)
    {
        $this->errors = $errors;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('admin@example.com')
                ->view('emails.moduleUploadErrors')
                ->with([
                    'errors' => $this->errors,
                ]);
        // return $this->view('view.name');
    }
}
