<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email Address - WorkHub</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            width: 100% !important;
            -webkit-text-size-adjust: none;
            -ms-text-size-adjust: none;
        }
        table {
            border-collapse: collapse;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f8fafc;
            padding: 40px 0;
        }
        .card {
            background-color: #ffffff;
            border-radius: 16px;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }
        .gradient-bar {
            height: 6px;
            background: #4e73df;
            background: linear-gradient(135deg, #4e73df 0%, #8f6cf0 100%);
        }
        .content-area {
            padding: 40px 32px;
        }
        .logo-area {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-text {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            letter-spacing: -0.5px;
        }
        .logo-dot {
            color: #4e73df;
            display: inline-block;
        }
        .title {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 20px;
            text-align: center;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 12px;
        }
        .message-body {
            font-size: 15px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 30px;
        }
        .btn-container {
            text-align: center;
            margin-bottom: 30px;
        }
        .btn-primary {
            display: inline-block;
            background-color: #4e73df;
            background: linear-gradient(135deg, #4e73df 0%, #8f6cf0 100%);
            color: #ffffff !important;
            padding: 14px 32px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 15px;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(78, 115, 223, 0.3);
            text-align: center;
        }
        .info-note {
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 30px;
            border-left: 3px solid #cbd5e1;
            padding-left: 12px;
        }
        .divider {
            border-top: 1px solid #e2e8f0;
            margin-top: 30px;
            margin-bottom: 24px;
        }
        .troubleshooting {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.5;
            word-break: break-all;
        }
        .troubleshooting a {
            color: #4e73df;
            text-decoration: none;
        }
        .footer {
            text-align: center;
            padding-top: 10px;
        }
        .footer-text {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.5;
            margin: 4px 0;
        }
    </style>
</head>
<body>
    <center class="wrapper">
        <table class="card" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td class="gradient-bar"></td>
            </tr>
            <tr>
                <td class="content-area">
                    <!-- Logo Section -->
                    <div class="logo-area">
                        <span class="logo-text">WorkHub<span class="logo-dot">.</span></span>
                    </div>

                    <!-- Header Title -->
                    <h1 class="title">Verify Your Email Address</h1>

                    <!-- Message Body -->
                    <div class="greeting">Hello {{ $name }},</div>
                    <div class="message-body">
                        Thanks for signing up for <strong>WorkHub</strong>! We're thrilled to have you on board.
                        To start organizing your tasks, creating project workflows, and collaborating with your team, 
                        please verify your email address by clicking the button below.
                    </div>

                    <!-- Action Button -->
                    <div class="btn-container">
                        <a href="{{ $url }}" class="btn-primary" target="_blank">Verify Email Address</a>
                    </div>

                    <!-- Informational Note -->
                    <div class="info-note">
                        This verification link will expire in 60 minutes. If you did not create a WorkHub account, 
                        you can safely ignore this email.
                    </div>

                    <div class="divider"></div>

                    <!-- Troubleshooting Link -->
                    <div class="troubleshooting">
                        If you're having trouble clicking the "Verify Email Address" button, copy and paste the URL below 
                        into your web browser:<br>
                        <a href="{{ $url }}" target="_blank">{{ $url }}</a>
                    </div>
                </td>
            </tr>
        </table>
        
        <!-- Footer -->
        <table class="footer" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; margin: 0 auto;">
            <tr>
                <td style="padding: 20px 0;">
                    <div class="footer-text">&copy; {{ date('Y') }} WorkHub. All rights reserved.</div>
                    <div class="footer-text">Made for premium, high-efficiency project management.</div>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
