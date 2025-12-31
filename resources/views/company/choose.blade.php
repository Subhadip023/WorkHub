@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">

        <div class="card shadow mb-4">
            <div class="card-header bg-dark text-white">
                Create Company
            </div>
            <div class="card-body">
                <form method="POST" action="/company/create">
                    @csrf
                    <input type="text" name="name" class="form-control mb-3" placeholder="Company Name" required>
                    <button class="btn btn-success w-100">Create Company</button>
                </form>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header bg-secondary text-white">
                Join Company
            </div>
            <div class="card-body">
                <form method="POST" action="/company/join">
                    @csrf
                    <input type="text" name="join_code" class="form-control mb-3" placeholder="Join Code" required>
                    <button class="btn btn-primary w-100">Join Company</button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
