<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Matainja Technology</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-yellow-300 via-green-400 to-blue-500 min-h-screen flex items-center justify-center">
<div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md">
    <h1 class="text-3xl font-bold text-center mb-6 text-green-700">Login</h1>

    @if($errors->any())
        @foreach($errors->all() as $error)
            <p class="text-red-600">{{ $error }}</p>
        @endforeach
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4 mt-4">
        @csrf
        <input type="email" name="email" placeholder="Email" class="w-full p-3 border rounded-lg" required>
        <input type="password" name="password" placeholder="Password" class="w-full p-3 border rounded-lg" required>
        <button type="submit" class="w-full bg-green-600 text-white p-3 rounded-lg hover:bg-green-700">Login</button>
    </form>

    <p class="mt-4 text-center">Don't have an account? <a href="{{ route('register') }}" class="text-green-700 font-bold">Register</a></p>
</div>
</body>
</html>
