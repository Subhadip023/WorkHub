@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Projects</h1>
    <a href="{{ route('projects.create') }}" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Add Project
    </a>
</div>

<!-- Content Column -->
    <div class="col-lg-12 mb-4">

        <!-- Project Card Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Projects</h6>
            </div>
            <div class="table-responsive mx-2">
                <table class="table table-bordered table-hover" width="98%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Theme</th>
                            <th>Tasks</th>
                            <th style="width: 140px;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                
                    <tbody>
                        @foreach ($projects as $project)
                            <tr>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center">
                                        <span class="mr-2 shadow-sm" style="display: inline-block; width: 16px; height: 16px; border-radius: 50%; background-color: {{ $project->theme }}; border: 1px solid rgba(0,0,0,0.15);"></span>
                                        <a href="{{ route('projects.show', $project) }}" class="font-weight-bold text-primary">
                                            {{ $project->name }}
                                        </a>
                                    </div>
                                </td>
                                <td class="align-middle">{!! Str::limit(strip_tags($project->description), 100) !!}</td>
                                <td class="align-middle">
                                    <span class="badge text-white px-2 py-1 shadow-sm" style="background-color: {{ $project->theme }}">
                                        {{ $project->theme }}
                                    </span>
                                </td>
                                <td class="align-middle">
                                    @php
                                        $total = $project->tasks->count();
                                        $completed = $project->tasks->where('is_completed', true)->count();
                                    @endphp
                                    <span class="badge badge-info p-2 font-weight-bold">
                                        {{ $completed }} / {{ $total }} Tasks
                                    </span>
                                </td>
                                <td class="text-center align-middle">
                                    <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-primary shadow-sm">
                                        <i class="fas fa-eye mr-1"></i> View Tasks
                                    </a>
                                </td>
                            </tr>
                         @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer py-2">
                <div class="d-flex justify-content-center">
                    {!! $projects->links() !!}
                </div>
            </div>
        </div>

       

    </div>

@endsection

@push('scripts')
<!-- Page level plugins -->
<script src="{{ asset('asset/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('asset/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

<!-- Page level custom scripts -->
<script src="{{ asset('asset/js/demo/datatables-demo.js') }}"></script>
@endpush

