@extends('layout.userlayout')
@section('content')

<div class="bg-white p-10 rounded-xl shadow-xl w-full max-w-md">
    <h2 class="text-3xl font-bold mb-6 text-center text-yellow-700">Setup Your Company</h2>

    <form method="POST" action="{{ route('company.store') }}">
        @csrf
        <input type="text" name="name" placeholder="Company Name" class="w-full p-3 border rounded mb-4" required>
        <button type="submit" class="w-full bg-yellow-600 text-white p-3 rounded hover:bg-yellow-700 transition mb-4">Create Company</button>
    </form>

    <hr class="my-4">

    <form method="POST" action="{{ route('company.join') }}">
        @csrf
        <input type="text" name="join_code" placeholder="Join Code" class="w-full p-3 border rounded mb-4" required>
        <button type="submit" class="w-full bg-green-600 text-white p-3 rounded hover:bg-green-700 transition">Join Company</button>
    </form>
</div>
@endsection