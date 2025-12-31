<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Matainja Technology</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            margin: 0;
            height: 100vh;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-container {
            background: #ffffff;
            width: 420px;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .company-name {
            text-align: center;
            font-size: 26px;
            font-weight: bold;
            color: #1e3c72;
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
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            opacity: 0.9;
        }

        .footer-text {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }

        .footer-text a {
            color: #1e3c72;
            text-decoration: none;
            font-weight: 600;
        }

        .error {
            background: #ffe5e5;
            color: #c0392b;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="company-name">Matainja Technology</div>
    <div class="company-tagline">Software Development Company</div>

    <h2>Create Account</h2>

    @if ($errors->any())
        <div class="error">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="password_confirmation" placeholder="Confirm Password" required>

        <button type="submit">Register</button>
    </form>

    <div class="footer-text">
        Already registered?
        <a href="{{ route('login') }}">Login</a>
    </div>
</div>

</body>
</html>
