@extends('layouts.admin')

@section('title', 'Company-Create')

@section('content')
    <div class="row justify-content-center align-items-center" >

        <div class="col-xl-10 col-lg-12 col-md-9">

            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
                        <div class="col-lg-6 d-none d-lg-block bg-login-image">
                            <div class="card-body">
                                <div class="text-center">
                                    <img class="img-fluid px-3 px-sm-4 mt-3 mb-4" style="width: 25rem;"
                                        src="{{ asset('asset/img/undraw_factory.svg') }}" alt="...">
                                </div>
                                
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4">Create a Company</h1>
                                </div>
                                <form class="user" method="POST" action="{{ route('companies.store') }}">
                                    @csrf
                                    <div class="form-group">
                                        <input type="text" name="name" class="form-control form-control-user"
                                           
                                            placeholder="Enter Company Name">
                                    </div>
                                   
                                    <button type="submit" class="btn btn-primary btn-user btn-block">
                                        Create
                                    </button>
                                    <div class="text-center">
                                        <a class="small " href="{{ route('companies.index') }}">Have a Code ? Join</a>
                                    </div>
                                    
                                </form>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

@endsection

@push('scripts')

@endpush
