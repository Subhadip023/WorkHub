<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notification->title }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #0f172a;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
            -webkit-font-smoothing: antialiased;
        }
        .email-wrapper {
            width: 100%;
            background-color: #f8fafc;
            padding: 40px 16px;
        }
        .email-card {
            max-width: 580px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
        }
        .email-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 28px 32px;
            text-align: left;
            border-bottom: 1px solid #334155;
        }
        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #10b981;
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            padding: 4px 10px;
            border-radius: 9999px;
            margin-bottom: 12px;
        }
        .header-title {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.02em;
        }
        .header-subtext {
            margin: 6px 0 0;
            font-size: 13px;
            color: #94a3b8;
        }
        .email-body {
            padding: 32px;
        }
        .notification-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #10b981;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .notification-title {
            margin: 0 0 8px 0;
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
        }
        .notification-message {
            margin: 0;
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
        }
        .meta-list {
            margin: 16px 0 0 0;
            padding: 12px 16px;
            background-color: #ffffff;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            font-size: 13px;
            color: #64748b;
        }
        .meta-item {
            margin-bottom: 6px;
        }
        .meta-item:last-child {
            margin-bottom: 0;
        }
        .meta-label {
            font-weight: 600;
            color: #1e293b;
        }
        .action-container {
            text-align: center;
            margin: 32px 0 16px;
        }
        .btn-primary {
            display: inline-block;
            background-color: #10b981;
            color: #ffffff !important;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
            transition: all 0.2s ease;
        }
        .email-footer {
            background-color: #f8fafc;
            padding: 24px 32px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.6;
        }
        .email-footer a {
            color: #64748b;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-card">
            <!-- Header -->
            <div class="email-header">
                <div class="brand-badge">
                    ⚡ WorkHub • {{ $workspaceName ?? 'Personal Space' }}
                </div>
                <h1 class="header-title">{{ $notification->title }}</h1>
                <p class="header-subtext">Automated workspace alert from WorkHub</p>
            </div>

            <!-- Body -->
            <div class="email-body">
                <div class="notification-box">
                    <h2 class="notification-title">{{ $notification->title }}</h2>
                    <p class="notification-message">{{ $notification->message }}</p>

                    @if(!empty($notification->data) && is_array($notification->data))
                        <div class="meta-list">
                            @if(isset($notification->data['due_date']))
                                <div class="meta-item">
                                    <span class="meta-label">📅 Deadline:</span> {{ $notification->data['due_date'] }}
                                </div>
                            @endif
                            @if(isset($notification->data['status']))
                                <div class="meta-item">
                                    <span class="meta-label">📊 Status:</span> {{ $notification->data['status'] }}
                                </div>
                            @endif
                            @if(isset($notification->data['priority']))
                                <div class="meta-item">
                                    <span class="meta-label">🔥 Priority:</span> {{ $notification->data['priority'] }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Call to Action -->
                @if(!empty($actionUrl))
                    <div class="action-container">
                        <a href="{{ $actionUrl }}" class="btn-primary" target="_blank">
                            Open in WorkHub →
                        </a>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="email-footer">
                <p style="margin: 0 0 6px 0;">
                    You received this email because email alerts are enabled for your account (<strong>{{ $recipient?->email ?? 'your email' }}</strong>).
                </p>
                <p style="margin: 0;">
                    To manage your email notification preferences, visit <a href="{{ url('/settings') }}">Account Settings</a>.
                </p>
            </div>
        </div>
    </div>
</body>
</html>

