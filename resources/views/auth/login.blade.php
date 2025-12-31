<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Matainja Technology</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            margin: 0;
            height: 100vh;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-container {
            background: #ffffff;
            width: 400px;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
        }

        .company-name {
            text-align: center;
            font-size: 26px;
            font-weight: bold;
            color: #764ba2;
        }

        .company-tagline {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-bottom: 30px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            opacity: 0.9;
        }

        .success {
            background: #e8f8f5;
            color: #16a085;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: center;
        }

        .footer-text {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }

        .footer-text a {
            color: #764ba2;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="company-name">Matainja Technology</div>
    <div class="company-tagline">Software Development Company</div>

    <h2>Login</h2>

    @if(session('success'))
        <div class="success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit">Login</button>
    </form>

    <div class="footer-text">
        New here?
        <a href="{{ route('register') }}">Create Account</a>
    </div>
</div>

</body>
</html>
