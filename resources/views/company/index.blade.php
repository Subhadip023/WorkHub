@extends('layout.userlayout')

@section('content')
<!-- <div class="min-h-screen flex items-center justify-center bg-gray-100"> -->

    <div class="w-full max-w-md bg-white rounded-lg shadow-md p-6">

        <h2 class="text-xl font-semibold text-center mb-4">
            Company Setup
        </h2>

        {{-- CREATE COMPANY --}}
        <div class="mb-5">
            <h3 class="text-sm font-medium mb-2">Create Company</h3>

            <form method="POST" action="{{ route('company.store') }}" class="space-y-3">
                @csrf
                <input
                    type="text"
                    name="name"
                    placeholder="Company Name"
                    required
                    class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring focus:border-blue-400"
                >

                <button
                    type="submit"
                    class="w-full bg-blue-600 text-white py-2 rounded-md text-sm hover:bg-blue-700 transition"
                >
                    Create Company
                </button>
            </form>
        </div>

        <div class="flex items-center my-4">
            <div class="flex-grow border-t"></div>
            <span class="px-2 text-xs text-gray-500">OR</span>
            <div class="flex-grow border-t"></div>
        </div>

        {{-- JOIN COMPANY --}}
        <div>
            <h3 class="text-sm font-medium mb-2">Join Company</h3>

            <form method="POST" action="{{ route('company.join') }}" class="space-y-3">
                @csrf
                <input
                    type="text"
                    name="join_code"
                    placeholder="Join Code"
                    required
                    class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring focus:border-green-400"
                >

                <button
                    type="submit"
                    class="w-full bg-green-600 text-white py-2 rounded-md text-sm hover:bg-green-700 transition"
                >
                    Join Company
                </button>
            </form>
        </div>

    </div>

<!-- </div> -->
@endsection
