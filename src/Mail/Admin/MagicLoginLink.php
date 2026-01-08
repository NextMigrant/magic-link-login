<?php

namespace NextMigrant\MagicLinkLogin\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MagicLoginLink extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $url)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Magic Login Link',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'magic-link-login::mail.admin.magic-login-link',
            with: [
                'loginLink' => $this->url,
            ],
        );
    }
}
