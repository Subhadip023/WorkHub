<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Matainja Technology</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-purple-400 via-pink-500 to-red-500 min-h-screen flex items-center justify-center">

<div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md">
    <h1 class="text-3xl font-bold text-center mb-6 text-purple-700">Register</h1>

    @if(session('success'))
        <p class="text-green-600 font-semibold">{{ session('success') }}</p>
    @endif

    @if($errors->any())
        @foreach($errors->all() as $error)
            <p class="text-red-600">{{ $error }}</p>
        @endforeach
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4 mt-4">
        @csrf
        <input type="text" name="name" placeholder="Name" class="w-full p-3 border rounded-lg" required>
        <input type="email" name="email" placeholder="Email" class="w-full p-3 border rounded-lg" required>
        <input type="password" name="password" placeholder="Password" class="w-full p-3 border rounded-lg" required>
        <input type="password" name="password_confirmation" placeholder="Confirm Password" class="w-full p-3 border rounded-lg" required>
        <button type="submit" class="w-full bg-purple-600 text-white p-3 rounded-lg hover:bg-purple-700">Register</button>
    </form>

    <p class="mt-4 text-center">Already have an account? <a href="{{ route('login') }}" class="text-purple-700 font-bold">Login</a></p>
</div>
</body>
</html>
