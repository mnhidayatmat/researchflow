<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Supervision Request</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #F5F5F4; margin: 0; padding: 0; }
        .wrapper { max-width: 560px; margin: 40px auto; padding: 0 16px; }
        .card { background: #fff; border-radius: 16px; overflow: hidden; border: 1px solid #E5E5E4; }
        .header { background: #D97706; padding: 32px 40px; text-align: center; }
        .header-icon { width: 52px; height: 52px; background: rgba(255,255,255,0.2); border-radius: 14px; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; }
        .header h1 { color: #fff; font-size: 20px; font-weight: 600; margin: 0; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 15px; color: #1C1917; margin-bottom: 16px; }
        .message { font-size: 14px; color: #57534E; line-height: 1.6; margin-bottom: 24px; }
        .info-box { background: #FAFAF9; border: 1px solid #E5E5E4; border-radius: 12px; padding: 20px 24px; margin-bottom: 28px; }
        .info-row { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid #F5F5F4; }
        .info-row:last-child { border-bottom: none; padding-bottom: 0; }
        .info-label { font-size: 12px; color: #78716C; font-weight: 500; }
        .info-value { font-size: 13px; color: #1C1917; font-weight: 600; }
        .role-badge { display: inline-block; font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 20px; background: #FEF3C7; color: #D97706; }
        .actions { display: flex; gap: 12px; margin-bottom: 24px; }
        .btn { display: inline-block; padding: 13px 28px; border-radius: 10px; font-size: 14px; font-weight: 600; text-decoration: none; text-align: center; flex: 1; }
        .btn-approve { background: #059669; color: #fff; }
        .btn-deny { background: #F5F5F4; color: #78716C; border: 1px solid #E5E5E4; }
        .note { font-size: 12px; color: #A8A29E; line-height: 1.6; }
        .footer { padding: 20px 40px; border-top: 1px solid #F5F5F4; text-align: center; }
        .footer p { font-size: 11px; color: #A8A29E; margin: 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <h1>Supervision Request</h1>
            </div>
            <div class="body">
                <p class="greeting">Dear {{ $supervisorName }},</p>
                <p class="message">
                    A student has registered on <strong>ResearchFlow</strong> and listed you as their
                    <strong>{{ $roleLabel }}</strong>. Please review the details below and approve or decline the request.
                </p>

                <div class="info-box">
                    <div class="info-row">
                        <span class="info-label">Student Name</span>
                        <span class="info-value">{{ $studentName }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Programme</span>
                        <span class="info-value">{{ $programmeName }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Your Role</span>
                        <span class="role-badge">{{ $roleLabel }}</span>
                    </div>
                </div>

                <div class="actions">
                    <a href="{{ $approveUrl }}" class="btn btn-approve">Approve</a>
                    <a href="{{ $denyUrl }}" class="btn btn-deny">Decline</a>
                </div>

                <p class="note">
                    This link will expire in 7 days. If you did not expect this request or believe it was sent in error,
                    you can safely ignore or decline it. No action is required if you decline.
                </p>
            </div>
            <div class="footer">
                <p>ResearchFlow &mdash; Academic Supervision Management</p>
            </div>
        </div>
    </div>
</body>
</html>
