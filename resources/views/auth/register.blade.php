@extends('layouts.auth')

@section('title', 'Register')

@section('content')
<div class="container" style="min-height: 100vh;">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">

        <div class="col-xl-10 col-lg-12 col-md-9">

            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
                        <div class="col-lg-6 d-none d-lg-block bg-register-image">
                            <div class="card-body">
                                <div class="text-center">
                                    <img class="img-fluid px-3 px-sm-4 mt-3 mb-4" style="width: 25rem;"
                                         src="{{ asset('asset/img/undraw_rocket.svg') }}" alt="...">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4">Create an Account!</h1>
                                </div>
                                <form class="user" method="POST" action="{{ route('register') }}">
                                    @csrf

                                    <div class="form-group">
                                        <input type="text" name="name" class="form-control form-control-user"
                                               id="exampleInputName" placeholder="Full Name" value="{{ old('name') }}" required autofocus autocomplete="name">
                                    </div>
                                    @error('name')
                                        <span class="text-danger small">{{$message}}</span>
                                    @enderror

                                    <div class="form-group">
                                        <input type="email" name="email" class="form-control form-control-user"
                                               id="exampleInputEmail" placeholder="Email Address" value="{{ old('email') }}" required autocomplete="username">
                                    </div>
                                    @error('email')
                                        <span class="text-danger small">{{$message}}</span>
                                    @enderror

                                    <div class="form-group">
                                        <input type="password" name="password" class="form-control form-control-user"
                                               id="exampleInputPassword" placeholder="Password" required autocomplete="new-password">
                                    </div>
                                    @error('password')
                                        <span class="text-danger small">{{$message}}</span>
                                    @enderror

                                    <div class="form-group">
                                        <input type="password" name="password_confirmation" class="form-control form-control-user"
                                               id="exampleRepeatPassword" placeholder="Confirm Password" required autocomplete="new-password">
                                    </div>
                                    @error('password_confirmation')
                                        <span class="text-danger small">{{$message}}</span>
                                    @enderror

                                    <button type="submit" class="btn btn-primary btn-user btn-block">
                                        Register Account
                                    </button>
                                    <hr>

                                </form>
                                <div class="text-center">
                                    <a class="small" href="{{ route('login') }}">Already have an account? Login!</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
