<?php

namespace App\Services;

use App\Mail\NewOrderNotificationMail;
use App\Mail\OrderStatusUpdatedMail;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Testing\Fakes\MailFake;

class OrderEmailService
{
    public function __construct(protected InquiryReplyService $inquiryReplyService) {}

    /**
     * Send a single New Order Placed email to info@menahem.co when a website order is created.
     */
    public function sendNewOrderAdminNotification(Order $order): void
    {
        // Idempotency guard: prevent duplicate New Order emails for the same order
        if (! Cache::add("order_new_email_sent:{$order->id}", true, now()->addHours(12))) {
            return;
        }

        $order->loadMissing('items');
        $recipient = 'info@menahem.co';
        $fromAddress = (string) config('mail.enquiry_from.address', 'info@menahem.co.in');
        $businessName = (string) (Setting::get('seller_name') ?: 'MENAHEM');

        try {
            $mailable = new NewOrderNotificationMail(
                order: $order,
                businessName: $businessName,
                fromAddress: $fromAddress,
            );

            $this->dispatchMailable($recipient, $businessName, $mailable);
        } catch (\Throwable $e) {
            Log::warning('New order admin email notification failed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send an Order Fulfillment Status update email to the customer.
     */
    public function sendOrderStatusUpdate(Order $order, string $previousStatus = ''): void
    {
        $customerEmail = trim((string) ($order->customer_email ?? ''));
        if ($customerEmail === '' || ! filter_var($customerEmail, FILTER_VALIDATE_EMAIL) || str_ends_with($customerEmail, '@menahem.local')) {
            return;
        }

        $order->loadMissing('items');
        $fromAddress = (string) config('mail.enquiry_from.address', 'info@menahem.co.in');
        $businessName = (string) (Setting::get('seller_name') ?: 'MENAHEM');

        try {
            $mailable = new OrderStatusUpdatedMail(
                order: $order,
                previousStatus: $previousStatus,
                businessName: $businessName,
                fromAddress: $fromAddress,
            );

            $this->dispatchMailable($customerEmail, $order->customer_name, $mailable);
        } catch (\Throwable $e) {
            Log::warning('Order status update email failed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->order_status,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Dispatch a mailable using the same SMTP pattern as customer enquiry emails.
     */
    protected function dispatchMailable(string $toAddress, ?string $toName, Mailable $mailable): void
    {
        if (Mail::getFacadeRoot() instanceof MailFake) {
            Mail::to($toAddress, $toName)->send($mailable);

            return;
        }

        if (app()->runningUnitTests() && config('mail.default') === 'array') {
            Mail::to($toAddress, $toName)->send($mailable);

            return;
        }

        $health = $this->inquiryReplyService->getSmtpHealth();
        if (! $health['ready']) {
            throw new \RuntimeException(implode(' ', $health['issues']));
        }

        Mail::mailer('smtp')->to($toAddress, $toName)->send($mailable);
    }
}
