@extends('layouts.auth')

@section('title', '404 Not Found')

@section('content')
<div class="container">

    <!-- Error Content -->
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <div class="error mx-auto" data-text="404" style="width: 7rem; height: 7rem; line-height: 7rem; font-size: 7rem; color: #5a5c69;">404</div>
            <p class="lead text-gray-800 mb-5">Page Not Found</p>
            <p class="text-gray-500 mb-0">It looks like you found a glitch in the matrix...</p>
            <a href="{{ route('dashboard') }}" class="btn btn-primary mt-4">&larr; Back to Dashboard</a>
        </div>
    </div>

</div>
@endsection
