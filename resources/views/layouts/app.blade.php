<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Matainja Technology</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background: #f4f6f9; }
        .navbar-brand { font-weight: bold; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-primary px-4">
    <span class="navbar-brand">Matainja Technology</span>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="btn btn-sm btn-light">Logout</button>
    </form>
</nav>

<div class="container mt-4">
    @yield('content')
</div>

</body>
</html>
