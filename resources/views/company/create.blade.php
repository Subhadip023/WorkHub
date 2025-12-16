<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Company | WorkHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<h2>Create Company</h2>

{{-- Success Message --}}
@if (session('success'))
    <p style="color: green;">
        {{ session('success') }}
    </p>
@endif

{{-- Validation Errors --}}
@if ($errors->any())
    <ul style="color: red;">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form method="POST" action="{{ route('company.store') }}">
    @csrf

    <label>Company Name</label><br>
    <input type="text" name="company_name" placeholder="Company Name" required>
    <br><br>

    <label>Owner Name</label><br>
    <input type="text" name="owner_name" placeholder="Owner Name" required>
    <br><br>

    <label>Owner Email</label><br>
    <input type="email" name="email" placeholder="Owner Email" required>
    <br><br>

    <button type="submit">Create Company</button>
</form>

</body>
</html>
