<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation to Join {{ $companyName }}</title>
    <style>
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8f9fc;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #e3e6f0;
        }
        .header {
            background-color: #4e73df;
            padding: 30px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .content {
            padding: 40px 30px;
            color: #5a5c69;
            line-height: 1.6;
        }
        .content p {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 16px;
        }
        .details-box {
            background-color: #f8f9fc;
            border-left: 4px solid #4e73df;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 4px;
        }
        .details-item {
            margin-bottom: 10px;
            font-size: 15px;
        }
        .details-item:last-child {
            margin-bottom: 0;
        }
        .details-label {
            font-weight: 700;
            color: #4e73df;
        }
        .message-box {
            background-color: #fdfefe;
            border: 1px dashed #dddfeb;
            padding: 15px;
            border-radius: 4px;
            font-style: italic;
            margin-bottom: 30px;
            color: #6e707e;
        }
        .button-container {
            text-align: center;
            margin-bottom: 30px;
        }
        .btn-primary {
            display: inline-block;
            background-color: #4e73df;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 4px 6px rgba(78, 115, 223, 0.2);
            transition: background-color 0.2s;
        }
        .btn-primary:hover {
            background-color: #2e59d9;
        }
        .footer {
            background-color: #f8f9fc;
            padding: 20px 30px;
            text-align: center;
            font-size: 13px;
            color: #858796;
            border-top: 1px solid #e3e6f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Workspace Invitation</h1>
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>You have been invited to join the <strong>{{ $companyName }}</strong> workspace on WorkHub.</p>
            
            <div class="details-box">
                <div class="details-item">
                    <span class="details-label">Organization:</span> {{ $companyName }}
                </div>
                <div class="details-item">
                    <span class="details-label">Invited By:</span> {{ $adminName }}
                </div>
                <div class="details-item">
                    <span class="details-label">Expiration:</span> {{ $expiry }}
                </div>
            </div>

            @if($customMessage)
                <p>Personal message from {{ $adminName }}:</p>
                <div class="message-box">
                    "{{ $customMessage }}"
                </div>
            @endif

            <p>Click the button below to join the workspace. The invitation code will be automatically pre-filled for you.</p>

            <div class="button-container">
                <a href="{{ $joinLink }}" class="btn-primary">Join Workspace</a>
            </div>

            <p>If you don't have a WorkHub account, you will be prompted to register first, then you can join using the code.</p>
        </div>
        <div class="footer">
            This is an automated email from WorkHub. Please do not reply directly to this email.
        </div>
    </div>
</body>
</html>
