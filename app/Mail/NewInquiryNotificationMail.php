<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewInquiryNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Inquiry $inquiry,
        public string $businessName = 'Menahem Home & Living',
        public string $fromAddress = 'info@menahem.co.in',
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $replyTo = [];
        $customerEmail = trim((string) ($this->inquiry->email ?? ''));
        if ($customerEmail !== '' && filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            $replyTo[] = new Address($customerEmail, $this->inquiry->name);
        }

        return new Envelope(
            from: new Address($this->fromAddress, $this->businessName),
            replyTo: $replyTo,
            subject: "New Website Enquiry: {$this->inquiry->resolvedSubject()} ({$this->inquiry->name})",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new-inquiry-notification',
        );
    }
}
