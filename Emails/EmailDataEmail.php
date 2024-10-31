<?php

declare(strict_types=1);

namespace Modules\Notify\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Notify\Datas\EmailData;

class EmailDataEmail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public EmailData $email_data) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->email_data->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'notify::emails.email-data-email',
            // Passiamo i dati da utilizzare nella vista
            with: [
                'body_html' => $this->email_data->body_html,
                'body' => $this->email_data->body,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
