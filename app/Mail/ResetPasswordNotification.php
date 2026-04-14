<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordNotification extends Mailable
{
    use Queueable, SerializesModels;

    // Token untuk reset password
    public $token;

    // Email user yang menerima
    public $userEmail;

    /**
     * Buat instance baru dari mailable
     */
    public function __construct($token, $userEmail)
    {
        $this->token = $token;
        $this->userEmail = $userEmail;
    }

    /**
     * Dapatkan envelope (subject, recipient, dsb)
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Password - LifeCare Poliklinik',
        );
    }

    /**
     * Dapatkan konten email
     */
    public function content(): Content
    {
        // Buat URL reset password dengan token
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => urlencode($this->userEmail),
        ], false));

        return new Content(
            view: 'emails.reset-password',
            with: [
                'resetUrl' => $resetUrl,
                'email' => $this->userEmail,
            ],
        );
    }

    /**
     * Dapatkan attachments untuk email
     */
    public function attachments(): array
    {
        return [];
    }
}
