<?php

namespace App\Services;

use App\Mail\InquiryReplyMail;
use App\Models\Inquiry;
use App\Models\InquiryReply;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InquiryReplyService
{
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
                ->where('status', 'sent')
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
            ->where('status', 'sent')
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

        try {
            $mailable = new InquiryReplyMail(
                inquiry: $inquiry,
                replyMessage: $cleanMessage,
                replySubject: $subject,
                businessName: $businessName,
                senderEmail: $senderEmail,
            );

            Mail::to($recipientEmail, $inquiry->name)->send($mailable);

            $reply = InquiryReply::create([
                'inquiry_id' => $inquiry->id,
                'user_id' => $sender?->id,
                'sender_name' => $senderDisplayName,
                'sender_email' => $senderEmail,
                'recipient_email' => $recipientEmail,
                'subject' => $subject,
                'message' => $cleanMessage,
                'status' => 'sent',
                'error_message' => null,
                'idempotency_key' => $cleanKey,
                'sent_at' => now(),
            ]);

            $inquiry->update(['status' => 'replied']);

            return [
                'success' => true,
                'duplicate' => false,
                'reply' => $reply,
            ];
        } catch (\Throwable $e) {
            $safeError = $this->sanitizeErrorMessage($e->getMessage());

            Log::error('Enquiry reply email sending failed', [
                'inquiry_id' => $inquiry->id,
                'recipient_email' => $recipientEmail,
                'error' => $safeError,
            ]);

            $reply = InquiryReply::create([
                'inquiry_id' => $inquiry->id,
                'user_id' => $sender?->id,
                'sender_name' => $senderDisplayName,
                'sender_email' => $senderEmail,
                'recipient_email' => $recipientEmail,
                'subject' => $subject,
                'message' => $cleanMessage,
                'status' => 'failed',
                'error_message' => $safeError,
                'idempotency_key' => null,
                'sent_at' => null,
            ]);

            $inquiry->update(['status' => 'failed']);

            return [
                'success' => false,
                'error' => $safeError,
                'reply' => $reply,
            ];
        }
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
