@extends('layouts.admin')

@section('title', 'Create Note')

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
    <h1 class="h3 mb-0 text-gray-800">Create New Note</h1>
    <a href="{{ request()->query('redirect_back', route('notes.index')) }}" class="btn btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm mr-1"></i> Cancel
    </a>
</div>

<div class="row">
    <div class="col-lg-9 col-12 mx-auto">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Note Details</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('notes.store') }}" method="POST" id="note-form">
                    @csrf
                    
                    @if(request()->query('redirect_back'))
                        <input type="hidden" name="redirect_back" value="{{ request()->query('redirect_back') }}">
                    @endif

                    <div class="form-group">
                        <label for="title" class="font-weight-bold text-gray-700">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="Enter note title..." required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="note_type_select" class="font-weight-bold text-gray-700">Note Type <span class="text-danger">*</span></label>
                            <select name="note_type" id="note_type_select" class="form-control" required>
                                <option value="4" {{ $defaultType == 4 ? 'selected' : '' }}>Personal Note</option>
                                <option value="1" {{ $defaultType == 1 ? 'selected' : '' }}>Project Note</option>
                                <option value="2" {{ $defaultType == 2 ? 'selected' : '' }}>Task Note</option>
                                <option value="3" {{ $defaultType == 3 ? 'selected' : '' }}>Organization Note</option>
                            </select>
                        </div>

                        <!-- Project Selection -->
                        <div class="col-md-6 form-group d-none" id="project_select_wrapper">
                            <label for="project_select" class="font-weight-bold text-gray-700">Select Project <span class="text-danger">*</span></label>
                            <select id="project_select" class="form-control">
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" {{ $defaultTypeId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Task Selection -->
                        <div class="col-md-6 form-group d-none" id="task_select_wrapper">
                            <label for="task_select" class="font-weight-bold text-gray-700">Select Task <span class="text-danger">*</span></label>
                            <select id="task_select" class="form-control">
                                @foreach($tasks as $t)
                                    <option value="{{ $t->id }}" {{ $defaultTypeId == $t->id ? 'selected' : '' }}>{{ $t->title }} (Project: {{ $t->project->name }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Organization Selection -->
                        <div class="col-md-6 form-group d-none" id="org_select_wrapper">
                            <label for="org_select" class="font-weight-bold text-gray-700">Select Organization <span class="text-danger">*</span></label>
                            <select id="org_select" class="form-control">
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}" {{ $defaultTypeId == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="editor-container" class="font-weight-bold text-gray-700">Description <span class="text-danger">*</span></label>
                        <input type="hidden" name="description" id="hidden-description">
                        <div id="editor-container" style="height: 350px;"></div>
                    </div>

                    <div class="text-right">
                        <a href="{{ request()->query('redirect_back', route('notes.index')) }}" class="btn btn-secondary mr-2">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="fas fa-save mr-1"></i> Save Note
                        </button>
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
            placeholder: 'Write your note contents here...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'blockquote', 'code-block'],
                    ['clean']
                ]
            }
        });

        $('#note-form').submit(function() {
            var descHtml = quill.root.innerHTML;
            if (descHtml === '<p><br></p>' || descHtml.trim() === '') {
                descHtml = '';
            }
            $('#hidden-description').val(descHtml);
        });

        // Dynamic target selection depending on note type
        $('#note_type_select').change(function() {
            var type = $(this).val();
            
            // Hide and disable all first
            $('#project_select_wrapper').addClass('d-none');
            $('#project_select').attr('name', '');
            
            $('#task_select_wrapper').addClass('d-none');
            $('#task_select').attr('name', '');
            
            $('#org_select_wrapper').addClass('d-none');
            $('#org_select').attr('name', '');

            // Show and enable selected
            if (type == 1) { // Project
                $('#project_select_wrapper').removeClass('d-none');
                $('#project_select').attr('name', 'note_type_id');
            } else if (type == 2) { // Task
                $('#task_select_wrapper').removeClass('d-none');
                $('#task_select').attr('name', 'note_type_id');
            } else if (type == 3) { // Organization
                $('#org_select_wrapper').removeClass('d-none');
                $('#org_select').attr('name', 'note_type_id');
            }
        });

        // Trigger change once on load to initialize correctly
        $('#note_type_select').trigger('change');
    });
</script>
@endpush
