<?php

declare(strict_types=1);

namespace Modules\Notify\Datas;

use Spatie\LaravelData\Data;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as MimeEmail;
use Webmozart\Assert\Assert;

class EmailData extends Data
{
    public string $to;

    public string $from;

    public string $from_email;

    public string $subject;

    public string $body_html;

    public ?string $body = null;

    /*
    public function __construct(
        string $to,
        ?string $from = null,
        ?string $from_email = null,
        string $subject,
        string $body_html,
        ?string $body = null
    ) {
        $this->to = $to;
        $this->from = $from ?? config('mail.from.name', 'No-Reply');
        $this->from_email = $from_email ?? config('mail.from.address');
        $this->subject = $subject;
        $this->body_html = $body_html;
        $this->body = $body ?? strip_tags($body_html);
    }
    */

    public function getFrom(): Address
    {
        Assert::string($fromEmail = $this->from_email ?? config('mail.from.address'));
        Assert::string($fromName = $this->from ?? config('mail.from.name', 'No-Reply'));

        return new Address($fromEmail, $fromName);
    }

    public function getMimeEmail(): MimeEmail
    {
        if (null == $this->body) {
            $this->body = strip_tags($this->body_html);
        }
        $email = (new MimeEmail())
            ->from($this->getFrom())
            ->to($this->to)
            ->subject($this->subject)
            ->html($this->body_html)
            ->text(strip_tags($this->body));

        return $email;
    }
}
