<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Website Enquiry</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background-color: #f7f8f6; padding: 24px 12px; margin: 0; color: #2B3326;">
    <div style="max-width: 620px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0;">
        <!-- Header -->
        <div style="background-color: #384230; padding: 24px; text-align: center; color: #ffffff;">
            <h1 style="margin: 0; font-size: 22px; letter-spacing: 3px; font-weight: 700;">MENAHEM</h1>
            <p style="margin: 6px 0 0; font-size: 12px; color: #B5C1A5;">New Customer Enquiry Received</p>
        </div>

        <!-- Body -->
        <div style="padding: 26px 24px;">
            <p style="margin: 0 0 16px; font-size: 14px; color: #333333; line-height: 1.5;">
                A visitor has submitted a new enquiry through the website contact form.
            </p>

            <!-- Customer & Enquiry Metadata -->
            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; margin-bottom: 20px;">
                <p style="margin: 4px 0; font-size: 13px;"><strong>Customer Name:</strong> {{ $inquiry->name }}</p>
                <p style="margin: 4px 0; font-size: 13px;"><strong>Email:</strong> {{ $inquiry->email ?: 'Not provided' }}</p>
                <p style="margin: 4px 0; font-size: 13px;"><strong>Phone / WhatsApp:</strong> {{ $inquiry->phone ?: 'Not provided' }}</p>
                <p style="margin: 4px 0; font-size: 13px;"><strong>Subject:</strong> {{ $inquiry->resolvedSubject() }}</p>
                <p style="margin: 4px 0; font-size: 13px;"><strong>Interest / Category:</strong> {{ ucwords(str_replace('_', ' ', $inquiry->type ?: 'general')) }}</p>
                @if($inquiry->product_name)
                    <p style="margin: 4px 0; font-size: 13px;"><strong>Product:</strong> {{ $inquiry->product_name }}</p>
                @endif
                <p style="margin: 4px 0; font-size: 13px;"><strong>Submitted At:</strong> {{ $inquiry->created_at?->format('d M Y, h:i A') ?? now()->format('d M Y, h:i A') }}</p>
            </div>

            <!-- Message Body -->
            <div style="margin-bottom: 24px;">
                <p style="margin: 0 0 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;">
                    Enquiry Message
                </p>
                <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-left: 3px solid #384230; padding: 14px 16px; font-size: 14px; line-height: 1.6; color: #2B3326; white-space: pre-line;">{{ $inquiry->message ?: 'No written message provided.' }}</div>
            </div>

            @if(!empty($inquiry->details))
                <div style="margin-bottom: 24px;">
                    <p style="margin: 0 0 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;">
                        Additional Details
                    </p>
                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 12px 14px; font-size: 12px; color: #475569;">
                        @foreach($inquiry->details as $key => $val)
                            <p style="margin: 3px 0;">
                                <strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong>
                                {{ is_array($val) ? json_encode($val) : $val }}
                            </p>
                        @endforeach
                    </div>
                </div>
            @endif

            <div style="text-align: center; margin: 26px 0 8px;">
                <a href="{{ route('admin.inquiries.show', $inquiry) }}"
                   style="background-color: #384230; color: #ffffff; text-decoration: none; padding: 12px 24px; font-weight: bold; font-size: 13px; display: inline-block; letter-spacing: 0.5px; text-transform: uppercase;">
                    View &amp; Reply in Admin Panel
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color: #f1f5f9; padding: 14px 24px; text-align: center; font-size: 11px; color: #64748b; border-top: 1px solid #e2e8f0;">
            &copy; {{ date('Y') }} {{ $businessName }}. Automated website enquiry alert.
        </div>
    </div>
</body>
</html>
