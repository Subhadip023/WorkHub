@extends('layouts.admin')

@section('title', 'Create Project')

@push('styles')
<!-- Quill rich text editor library styles -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-container {
        font-size: 1rem;
        border-bottom-left-radius: 0.35rem;
        border-bottom-right-radius: 0.35rem;
    }
    .ql-toolbar {
        border-top-left-radius: 0.35rem;
        border-top-right-radius: 0.35rem;
        background-color: #f8f9fc;
    }
</style>
@endpush

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Create New Project</h1>
    <a href="{{ route('projects.index') }}" class="btn btn-secondary btn-sm shadow-sm">
        <i class="fas fa-arrow-left fa-sm mr-1"></i> Back to Projects
    </a>
</div>

<div class="row d-flex align-items-center justify-content-center ">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Project Details</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('projects.store') }}" method="POST" id="addProjectForm">
                    @csrf
                    
                    <div class="row">
                        <div class="form-group col-md-10">
                            <label for="name" class="font-weight-bold text-gray-700">Project Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="Enter project name">
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>      
                        <div class="form-group col-md-2">
                            <label for="theme" class="font-weight-bold text-gray-700">Theme <span class="text-danger">*</span></label>
                            <input type="color" class="form-control" id="theme" name="theme" value="{{ old('theme', '#4e73df') }}" style="padding: 3px; height: 38px;">
                            @error('theme')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="company_id" class="font-weight-bold text-gray-700">Workspace / Organization <span class="text-danger">*</span></label>
                            <select class="form-control @error('company_id') is-invalid @enderror" id="company_id" name="company_id" required>
                                <option value="personal" {{ old('company_id') == 'personal' ? 'selected' : '' }}>Personal Space (Default)</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                        Organization: {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="status" class="font-weight-bold text-gray-700">Status <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>To Do</option>
                                <option value="2" {{ old('status') == '2' ? 'selected' : '' }}>In Progress</option>
                                <option value="3" {{ old('status') == '3' ? 'selected' : '' }}>Completed</option>
                                <option value="4" {{ old('status') == '4' ? 'selected' : '' }}>On Hold</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="priority" class="font-weight-bold text-gray-700">Priority <span class="text-danger">*</span></label>
                            <select class="form-control @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                <option value="1" {{ old('priority') == '1' ? 'selected' : '' }}>Low</option>
                                <option value="2" {{ old('priority', '2') == '2' ? 'selected' : '' }}>Medium</option>
                                <option value="3" {{ old('priority') == '3' ? 'selected' : '' }}>High</option>
                                <option value="4" {{ old('priority') == '4' ? 'selected' : '' }}>Urgent</option>
                            </select>
                            @error('priority')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="editor-container" class="font-weight-bold text-gray-700">Description <span class="text-danger">*</span></label>
                        <input type="hidden" name="description" id="hidden-description" value="{{ old('description') }}">
                        <div id="editor-container" style="height: 250px;">{!! old('description') !!}</div>
                        @error('description')
                            <span class="text-danger small d-block mt-2">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary shadow-sm mr-2">
                            <i class="fas fa-save mr-1"></i> Create Project
                        </button>
                        <a href="{{ route('projects.index') }}" class="btn btn-secondary shadow-sm">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Quill rich text editor library script -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    $(document).ready(function() {
        var quill = new Quill('#editor-container', {
            theme: 'snow',
            placeholder: 'Write a description for your project...'
        });

        $('#addProjectForm').submit(function() {
            var descHtml = quill.root.innerHTML;
            if (descHtml === '<p><br></p>' || descHtml.trim() === '') {
                descHtml = '';
            }
            $('#hidden-description').val(descHtml);
        });
    });
</script>
@endpush
