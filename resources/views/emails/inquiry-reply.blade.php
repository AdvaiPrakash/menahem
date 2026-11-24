<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $replySubject }}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background-color: #f7f8f6; padding: 24px 12px; margin: 0; color: #2B3326;">
    <div style="max-width: 620px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0;">
        <!-- Brand Header -->
        <div style="background-color: #384230; padding: 24px; text-align: center; color: #ffffff;">
            <h1 style="margin: 0; font-size: 22px; letter-spacing: 3px; font-weight: 700;">MENAHEM</h1>
            <p style="margin: 6px 0 0; font-size: 12px; color: #B5C1A5;">{{ $businessName }}</p>
        </div>

        <!-- Main Reply Body -->
        <div style="padding: 28px 24px;">
            <p style="margin: 0 0 14px; font-size: 15px; font-weight: 700; color: #2B3326;">
                Dear {{ $inquiry->name }},
            </p>

            <div style="font-size: 14px; line-height: 1.65; color: #333333; margin-bottom: 24px;">
                {!! nl2br(e($replyMessage)) !!}
            </div>

            <p style="margin: 0 0 22px; font-size: 13px; color: #5C6654; line-height: 1.5;">
                Warm regards,<br>
                <strong style="color: #2B3326;">{{ $businessName }}</strong><br>
                <span style="font-size: 12px;">Email: {{ $senderEmail }}</span>
            </p>

            <!-- Quoted Original Enquiry Reference -->
            <div style="background-color: #f8fafc; border-left: 3px solid #74826B; border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; padding: 14px 16px;">
                <p style="margin: 0 0 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;">
                    Your Original Enquiry ({{ $inquiry->created_at?->format('d M Y, h:i A') ?? '' }})
                </p>
                <p style="margin: 0 0 6px; font-size: 12px; color: #475569;">
                    <strong>Subject:</strong> {{ $inquiry->resolvedSubject() }}
                </p>
                @if($inquiry->message)
                    <p style="margin: 0; font-size: 12px; line-height: 1.5; color: #475569; white-space: pre-line;">{{ $inquiry->message }}</p>
                @endif
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color: #f1f5f9; padding: 16px 24px; text-align: center; font-size: 11px; color: #64748b; border-top: 1px solid #e2e8f0;">
            <p style="margin: 0 0 4px;">
                You can reply directly to this email ({{ $senderEmail }}) if you need any further assistance.
            </p>
            <p style="margin: 0;">
                &copy; {{ date('Y') }} {{ $businessName }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
