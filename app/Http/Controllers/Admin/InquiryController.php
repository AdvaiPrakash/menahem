<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Services\InquiryReplyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function __construct(protected InquiryReplyService $replyService) {}

    /**
     * Display a listing of customer enquiries.
     */
    public function index(Request $request): View
    {
        $query = Inquiry::query()->withCount([
            'replies as sent_replies_count' => static fn ($q) => $q->where('status', 'sent'),
            'replies as failed_replies_count' => static fn ($q) => $q->where('status', 'failed'),
        ]);

        if ($status = $request->input('status')) {
            if ($status === 'pending' || $status === 'new') {
                $query->whereIn('status', ['new', 'pending']);
            } else {
                $query->where('status', $status);
            }
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('product_name', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $inquiries = $query->latest()->paginate(20)->withQueryString();

        $statusCounts = [
            'all' => Inquiry::count(),
            'pending' => Inquiry::whereIn('status', ['new', 'pending'])->count(),
            'replied' => Inquiry::where('status', 'replied')->count(),
            'failed' => Inquiry::where('status', 'failed')->count(),
            'closed' => Inquiry::where('status', 'closed')->count(),
        ];

        return view('admin.inquiries.index', [
            'inquiries' => $inquiries,
            'statusCounts' => $statusCounts,
            'currentStatus' => $status,
            'currentType' => $type,
            'search' => $search,
        ]);
    }

    /**
     * Display the specified enquiry, conversation history, and direct reply form.
     */
    public function show(Inquiry $inquiry): View
    {
        $inquiry->load(['replies.user']);

        return view('admin.inquiries.show', [
            'inquiry' => $inquiry,
            'defaultReplySubject' => $this->replyService->formatReplySubject($inquiry),
            'idempotencyKey' => (string) Str::uuid(),
            'senderEmail' => (string) config('mail.enquiry_from.address', 'info@menahem.co.in'),
        ]);
    }

    /**
     * Send an email reply directly from the Admin Panel and persist conversation history.
     */
    public function reply(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:2', 'max:10000'],
            'idempotency_key' => ['nullable', 'string', 'max:100'],
        ], [
            'message.required' => 'Please enter a reply message.',
            'message.min' => 'Reply message must be at least 2 characters.',
        ]);

        $result = $this->replyService->sendReply(
            inquiry: $inquiry,
            sender: $request->user(),
            replyMessage: $validated['message'],
            customSubject: $validated['subject'] ?? null,
            idempotencyKey: $validated['idempotency_key'] ?? null,
        );

        if ($result['success']) {
            $flash = ($result['duplicate'] ?? false)
                ? 'This reply was already sent.'
                : "Reply sent successfully to {$inquiry->email} from ".config('mail.enquiry_from.address', 'info@menahem.co.in').'.';

            return redirect()->route('admin.inquiries.show', $inquiry)
                ->with('success', $flash);
        }

        return redirect()->route('admin.inquiries.show', $inquiry)
            ->withInput($request->only(['subject', 'message']))
            ->with('error', 'Failed to send reply email: '.($result['error'] ?? 'Unknown SMTP error.'));
    }

    /**
     * Update enquiry status and admin notes.
     */
    public function updateStatus(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:new,pending,replied,failed,contacted,closed'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        $inquiry->update($validated);

        return back()->with('success', 'Enquiry status updated successfully.');
    }

    /**
     * Remove the specified enquiry.
     */
    public function destroy(Inquiry $inquiry): RedirectResponse
    {
        $inquiry->delete();

        return redirect()->route('admin.inquiries.index')
            ->with('success', 'Enquiry deleted successfully.');
    }
}
