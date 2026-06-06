<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $emailSubject }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        .wrapper {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .header {
            background: linear-gradient(135deg, #4e73df, #224abe);
            padding: 32px 40px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            font-size: 22px;
            margin: 0;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .header p {
            color: rgba(255,255,255,0.75);
            font-size: 13px;
            margin: 6px 0 0;
        }
        .body {
            padding: 36px 40px;
            line-height: 1.8;
            font-size: 15px;
            color: #444;
        }
        .body p {
            margin: 0 0 16px;
        }
        .footer {
            background: #f8f9fc;
            border-top: 1px solid #e3e6f0;
            padding: 20px 40px;
            text-align: center;
            font-size: 12px;
            color: #aaa;
        }
        .footer a {
            color: #4e73df;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
            <p>Daily Digest — {{ now()->format('l, F j, Y') }}</p>
        </div>
        <div class="body">
            {!! nl2br(e($emailBody)) !!}
        </div>
        <div class="footer">
            &copy; {{ now()->year }} {{ config('app.name') }}. All rights reserved.<br>
            <a href="{{ config('app.url') }}">Visit WorkHub</a>
        </div>
    </div>
</body>
</html>
