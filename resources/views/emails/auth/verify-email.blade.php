<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify your email</title>
</head>
<body style="margin:0;padding:0;background:#f7f7f5;font-family:Segoe UI,Arial,sans-serif;color:#1f2937;">
    <div style="max-width:600px;margin:0 auto;padding:32px 16px;">
        <div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:20px;overflow:hidden;">
            <div style="padding:28px 28px 20px;background:linear-gradient(135deg,#fff7ed,#ffffff);border-bottom:1px solid #e5e7eb;">
                <div style="display:inline-block;padding:10px 14px;background:#d97706;color:#ffffff;border-radius:14px;font-weight:600;">
                    ResearchFlow
                </div>
                <h1 style="margin:18px 0 8px;font-size:24px;line-height:1.25;">Verify your email address</h1>
                <p style="margin:0;font-size:14px;line-height:1.6;color:#6b7280;">
                    Complete your ResearchFlow registration by confirming your email.
                </p>
            </div>

            <div style="padding:28px;">
                <p style="margin:0 0 16px;font-size:14px;line-height:1.7;">Hello {{ $recipientName }},</p>
                <p style="margin:0 0 20px;font-size:14px;line-height:1.7;">
                    Click the button below to verify your email address. This verification link expires in 24 hours.
                </p>

                <p style="margin:0 0 24px;">
                    <a href="{{ $verificationUrl }}" style="display:inline-block;padding:12px 18px;background:#d97706;color:#ffffff;text-decoration:none;border-radius:12px;font-size:14px;font-weight:600;">
                        Verify Email
                    </a>
                </p>

                <p style="margin:0 0 10px;font-size:13px;line-height:1.7;color:#6b7280;">
                    If the button does not work, copy and paste this link into your browser:
                </p>
                <p style="margin:0;font-size:12px;line-height:1.7;word-break:break-all;color:#1f2937;">
                    <a href="{{ $verificationUrl }}" style="color:#d97706;">{{ $verificationUrl }}</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
