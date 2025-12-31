<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WorkHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<header class="bg-white shadow p-4 flex justify-between">
    <h1 class="font-bold text-xl">WorkHub</h1>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="text-red-600 font-semibold">Logout</button>
    </form>
</header>

<main class="p-8">
    @yield('content')
</main>

</body>
</html>
