@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Projects</h1>
    <button class="btn btn-primary" data-toggle="modal" data-target="#addProjectModal">Add Project</button>
</div>

<!-- Content Column -->
    <div class="col-lg-12 mb-4">

        <!-- Project Card Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Projects</h6>
            </div>
            <div class="table-responsive mx-2">
                <table class="table table-bordered" id="dataTable" width="98%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Theme</th>
                            <th>Tasks</th>
                        </tr>
                    </thead>
                
                    <tbody>
                        @foreach ($projects as $project)
                            <tr style="background-color: {{$project->theme}};">
                                <td>{{ $project->name }}</td>
                                <td>{{ $project->description }}</td>
                                <td>{{ $project->theme }}</td>
                                <td>0</td>
                            </tr>
                         @endforeach
                    </tbody>
                </table>
            </div>
        </div>

       

    </div>
    {{-- add project modal --}}
    <div class="modal fade" id="addProjectModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add Project</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"></button>
                        <span aria-hidden="true">×</span>
                </div>
                <div class="modal-body">
                    <form action="{{ route('projects.store') }}" method="POST" id="addProjectForm">
                        @csrf
                        <div class="row">
                            <div class="form-group col-10">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name">
                        </div>      
                        <div class="col-2">
                            <label for="theme">Theme</label>
                            <input type="color" class="form-control" id="theme" name="theme" value="#326499" style="padding: 0;!important">
                        </div>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea type="" class="form-control" id="description" name="description" rows="4"></textarea>
                        </div>
                       
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" onclick="document.getElementById('addProjectForm').submit();">Add</a>
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

