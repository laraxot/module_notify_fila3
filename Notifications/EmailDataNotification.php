<?php

declare(strict_types=1);

namespace Modules\Notify\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Notify\Datas\EmailData;

class EmailDataNotification extends Notification
{
    use Queueable;

    protected EmailData $emailData;

    public function __construct(EmailData $emailData)
    {
        $this->emailData = $emailData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $mailMessage = (new MailMessage())
            ->subject($this->emailData->subject)
            ->line($this->emailData->body);

        if (! empty($this->emailData->body_html)) {
            $mailMessage->view('notify::emails.template', [
                'content' => $this->emailData->body_html,
            ]);
        }

        if (! empty($this->emailData->from_email) && ! empty($this->emailData->from)) {
            $mailMessage->from($this->emailData->from_email, $this->emailData->from);
        }

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'to' => $this->emailData->to,
            'from' => $this->emailData->from,
            'from_email' => $this->emailData->from_email,
            'subject' => $this->emailData->subject,
            'body' => $this->emailData->body,
        ];
    }
}
