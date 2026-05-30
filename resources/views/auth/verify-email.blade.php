@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
<div class="container" style="min-height: 100vh;">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">

        <div class="col-xl-10 col-lg-12 col-md-9">

            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
                        <div class="col-lg-6 d-none d-lg-block bg-login-image">
                            <div class="card-body">
                                <div class="text-center">
                                    <img class="img-fluid px-3 px-sm-4 mt-3 mb-4" style="width: 25rem;"
                                         src="{{ asset('asset/img/undraw_to-do-list_o3jf.svg') }}" alt="...">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4">{{ __('Verify Email Address') }}</h1>
                                </div>
                                <div class="text-gray-600 mb-4 text-center">
                                    {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
                                </div>

                                @if (session('status') == 'verification-link-sent')
                                    <div class="alert alert-success text-center small mb-4" role="alert">
                                        {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                                    </div>
                                @endif

                                <hr>

                                <div class="mt-4">
                                    <form method="POST" action="{{ route('verification.send') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            {{ __('Resend Verification Email') }}
                                        </button>
                                    </form>
                                </div>

                                <hr>

                                <div class="text-center mt-3">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-link small text-gray-600 hover:text-gray-900 p-0" style="text-decoration: underline;">
                                            {{ __('Log Out') }}
                                        </button>
                                    </form>
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
