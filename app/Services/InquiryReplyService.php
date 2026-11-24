<?php

namespace App\Services;

use App\Mail\InquiryReplyMail;
use App\Mail\NewInquiryNotificationMail;
use App\Models\Inquiry;
use App\Models\InquiryReply;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Testing\Fakes\MailFake;

class InquiryReplyService
{
    /**
     * Inspect current server SMTP configuration without exposing secrets.
     *
     * @return array{
     *     ready: bool,
     *     default_mailer: string,
     *     host: string,
     *     port: int,
     *     username: string,
     *     password_set: bool,
     *     issues: array<int, string>
     * }
     */
    public function getSmtpHealth(): array
    {
        $host = trim((string) config('mail.mailers.smtp.host', ''));
        $port = (int) config('mail.mailers.smtp.port', 0);
        $username = trim((string) config('mail.mailers.smtp.username', ''));
        $password = (string) config('mail.mailers.smtp.password', '');
        $defaultMailer = (string) config('mail.default', 'log');

        $passwordSet = $password !== '' && strtolower($password) !== 'null';
        $issues = [];

        if ($host === '' || $host === '127.0.0.1' || $host === 'localhost') {
            $issues[] = 'MAIL_HOST is not pointing to smtp.hostinger.com.';
        }
        if ($port <= 0) {
            $issues[] = 'MAIL_PORT is not configured.';
        }
        if ($username === '' || strtolower($username) === 'null') {
            $issues[] = 'MAIL_USERNAME is not configured.';
        }
        if (! $passwordSet) {
            $issues[] = 'MAIL_PASSWORD is missing in server environment (.env).';
        }

        return [
            'ready' => $issues === [],
            'default_mailer' => $defaultMailer,
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'password_set' => $passwordSet,
            'issues' => $issues,
        ];
    }

    /**
     * Send the new customer enquiry notification email to the business mailbox.
     *
     * @return array{success: bool, error?: string, message_id?: ?string}
     */
    public function sendAdminNotification(Inquiry $inquiry): array
    {
        $adminEmail = (string) config('mail.enquiry_from.address', 'info@menahem.co.in');
        $businessName = trim((string) Setting::get('seller_name', config('mail.enquiry_from.name', 'Menahem Home & Living')));

        try {
            $mailable = new NewInquiryNotificationMail(
                inquiry: $inquiry,
                businessName: $businessName,
                fromAddress: $adminEmail,
            );

            $messageId = $this->dispatchMailable($adminEmail, $businessName, $mailable);

            $inquiry->update([
                'notification_status' => 'accepted',
                'notification_error' => null,
                'notified_at' => now(),
            ]);

            return [
                'success' => true,
                'message_id' => $messageId,
            ];
        } catch (\Throwable $e) {
            $safeError = $this->sanitizeErrorMessage($e->getMessage());

            Log::warning('New enquiry admin email notification failed', [
                'inquiry_id' => $inquiry->id,
                'error' => $safeError,
            ]);

            $inquiry->update([
                'notification_status' => 'failed',
                'notification_error' => $safeError,
            ]);

            return [
                'success' => false,
                'error' => $safeError,
            ];
        }
    }

    /**
     * Send an email reply to a customer enquiry via Hostinger SMTP and persist conversation history.
     *
     * @return array{
     *     success: bool,
     *     duplicate?: bool,
     *     error?: string,
     *     reply?: InquiryReply
     * }
     */
    public function sendReply(
        Inquiry $inquiry,
        ?User $sender,
        string $replyMessage,
        ?string $customSubject = null,
        ?string $idempotencyKey = null
    ): array {
        $recipientEmail = trim((string) $inquiry->email);
        if ($recipientEmail === '' || ! filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'error' => 'This enquiry does not have a valid customer email address.',
            ];
        }

        $cleanMessage = trim($replyMessage);
        if ($cleanMessage === '') {
            return [
                'success' => false,
                'error' => 'Please enter a reply message before sending.',
            ];
        }

        // Prevent duplicate replies caused by repeated button clicks / double submissions
        $cleanKey = $idempotencyKey ? trim($idempotencyKey) : null;
        if ($cleanKey) {
            $existing = InquiryReply::where('inquiry_id', $inquiry->id)
                ->where('idempotency_key', $cleanKey)
                ->whereIn('status', ['sent', 'accepted'])
                ->first();

            if ($existing) {
                return [
                    'success' => true,
                    'duplicate' => true,
                    'reply' => $existing,
                ];
            }
        }

        $recentDuplicate = InquiryReply::where('inquiry_id', $inquiry->id)
            ->whereIn('status', ['sent', 'accepted'])
            ->where('message', $cleanMessage)
            ->where('created_at', '>=', now()->subSeconds(15))
            ->first();

        if ($recentDuplicate) {
            return [
                'success' => true,
                'duplicate' => true,
                'reply' => $recentDuplicate,
            ];
        }

        $senderEmail = (string) config('mail.enquiry_from.address', 'info@menahem.co.in');
        $businessName = trim((string) Setting::get('seller_name', config('mail.enquiry_from.name', 'Menahem Home & Living')));
        $senderDisplayName = $sender?->name ?: $businessName;
        $subject = $this->formatReplySubject($inquiry, $customSubject);

        // Create pending reply record first so status reflects actual transition
        $reply = InquiryReply::create([
            'inquiry_id' => $inquiry->id,
            'user_id' => $sender?->id,
            'sender_name' => $senderDisplayName,
            'sender_email' => $senderEmail,
            'recipient_email' => $recipientEmail,
            'subject' => $subject,
            'message' => $cleanMessage,
            'status' => 'pending',
            'error_message' => null,
            'smtp_message_id' => null,
            'idempotency_key' => $cleanKey,
            'sent_at' => null,
        ]);

        return $this->attemptReplyDelivery($inquiry, $reply, $businessName);
    }

    /**
     * Retry sending a failed or pending reply without creating a duplicate conversation entry.
     *
     * @return array{
     *     success: bool,
     *     duplicate?: bool,
     *     error?: string,
     *     reply: InquiryReply
     * }
     */
    public function retryReply(Inquiry $inquiry, InquiryReply $reply): array
    {
        if ($reply->isSent()) {
            return [
                'success' => true,
                'duplicate' => true,
                'reply' => $reply,
            ];
        }

        $recipientEmail = trim((string) $inquiry->email);
        if ($recipientEmail === '' || ! filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'error' => 'This enquiry does not have a valid customer email address.',
                'reply' => $reply,
            ];
        }

        // Sync recipient to original enquiry email (never allow recipient tampering)
        $reply->update([
            'recipient_email' => $recipientEmail,
            'status' => 'pending',
            'error_message' => null,
        ]);

        $businessName = trim((string) Setting::get('seller_name', config('mail.enquiry_from.name', 'Menahem Home & Living')));

        return $this->attemptReplyDelivery($inquiry, $reply, $businessName);
    }

    /**
     * Format the email subject as "Re: <Original Subject>" without duplicating "Re:".
     */
    public function formatReplySubject(Inquiry $inquiry, ?string $customSubject = null): string
    {
        $base = trim((string) ($customSubject ?: $inquiry->resolvedSubject()));
        if ($base === '') {
            $base = $inquiry->resolvedSubject();
        }

        if (preg_match('/^re:\s*/i', $base)) {
            return $base;
        }

        return "Re: {$base}";
    }

    /**
     * Attempt SMTP delivery for an existing InquiryReply record and update statuses accurately.
     *
     * @return array{success: bool, duplicate?: bool, error?: string, reply: InquiryReply}
     */
    protected function attemptReplyDelivery(Inquiry $inquiry, InquiryReply $reply, string $businessName): array
    {
        try {
            $mailable = new InquiryReplyMail(
                inquiry: $inquiry,
                replyMessage: $reply->message,
                replySubject: $reply->subject,
                businessName: $businessName,
                senderEmail: $reply->sender_email,
            );

            $messageId = $this->dispatchMailable($reply->recipient_email, $inquiry->name, $mailable);

            $reply->update([
                'status' => 'accepted',
                'error_message' => null,
                'smtp_message_id' => $messageId,
                'sent_at' => now(),
            ]);

            $inquiry->update(['status' => 'replied']);

            return [
                'success' => true,
                'duplicate' => false,
                'reply' => $reply->fresh(),
            ];
        } catch (\Throwable $e) {
            $safeError = $this->sanitizeErrorMessage($e->getMessage());

            Log::error('Enquiry reply email sending failed', [
                'inquiry_id' => $inquiry->id,
                'reply_id' => $reply->id,
                'recipient_email' => $reply->recipient_email,
                'error' => $safeError,
            ]);

            $reply->update([
                'status' => 'failed',
                'error_message' => $safeError,
                'sent_at' => null,
            ]);

            $inquiry->update(['status' => 'failed']);

            return [
                'success' => false,
                'error' => $safeError,
                'reply' => $reply->fresh(),
            ];
        }
    }

    /**
     * Dispatch a mailable using real Hostinger SMTP in runtime environments (never silently logging).
     */
    protected function dispatchMailable(string $toAddress, ?string $toName, Mailable $mailable): ?string
    {
        // Preserve Laravel MailFake and array-driver testing behavior in automated tests
        if (Mail::getFacadeRoot() instanceof MailFake) {
            Mail::to($toAddress, $toName)->send($mailable);

            return null;
        }

        if (app()->runningUnitTests() && config('mail.default') === 'array') {
            $sent = Mail::to($toAddress, $toName)->send($mailable);

            return $this->extractMessageId($sent);
        }

        $health = $this->getSmtpHealth();
        if (! $health['ready']) {
            throw new \RuntimeException(implode(' ', $health['issues']));
        }

        // Force the 'smtp' mailer so a stale MAIL_MAILER=log in .env or config cache cannot fake success
        $sent = Mail::mailer('smtp')->to($toAddress, $toName)->send($mailable);

        return $this->extractMessageId($sent);
    }

    /**
     * Extract the RFC Message-ID header (falling back to the SMTP queue ID) from a SentMessage.
     */
    protected function extractMessageId(?SentMessage $sent): ?string
    {
        if (! $sent) {
            return null;
        }

        try {
            $original = $sent->getSymfonySentMessage()->getOriginalMessage();
            if (method_exists($original, 'getHeaders')) {
                $header = $original->getHeaders()->get('Message-ID');
                if ($header) {
                    $val = trim((string) $header->getBodyAsString());
                    if ($val !== '') {
                        return $val;
                    }
                }
            }
        } catch (\Throwable) {
            // Fall back to transport message ID below
        }

        $fallback = trim((string) $sent->getMessageId());

        return $fallback !== '' ? $fallback : null;
    }

    /**
     * Redact any sensitive credential strings from SMTP exception messages before logging or storing.
     */
    protected function sanitizeErrorMessage(string $rawMessage): string
    {
        $sanitized = trim($rawMessage);

        $password = (string) config('mail.mailers.smtp.password', '');
        if ($password !== '') {
            $sanitized = str_replace($password, '[REDACTED]', $sanitized);
        }

        if ($sanitized === '') {
            return 'Unable to connect to the Hostinger SMTP server. Please verify SMTP credentials and try again.';
        }

        return mb_substr($sanitized, 0, 500);
    }
}
