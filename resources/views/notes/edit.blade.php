@extends('layouts.admin')

@section('title', 'Edit Note')

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
    <h1 class="h3 mb-0 text-gray-800">Edit Note</h1>
    <a href="{{ request()->query('redirect_back', route('notes.index')) }}" class="btn btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm mr-1"></i> Cancel
    </a>
</div>

<div class="row">
    <div class="col-lg-9 col-12 mx-auto">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Edit Details</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('notes.update', $note) }}" method="POST" id="note-form">
                    @csrf
                    @method('PATCH')
                    
                    @if(request()->query('redirect_back'))
                        <input type="hidden" name="redirect_back" value="{{ request()->query('redirect_back') }}">
                    @endif

                    <div class="form-group">
                        <label for="title" class="font-weight-bold text-gray-700">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ $note->title }}" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <span class="font-weight-bold text-gray-700 mr-2">Note Type:</span>
                            @if($note->note_type == 4)
                                <span class="badge badge-success px-2 py-1 font-weight-bold shadow-sm">Personal</span>
                            @elseif($note->note_type == 1)
                                <span class="badge badge-primary px-2 py-1 font-weight-bold shadow-sm">Project: {{ $note->noteable ? $note->noteable->name : 'Deleted Project' }}</span>
                            @elseif($note->note_type == 2)
                                <span class="badge badge-warning px-2 py-1 font-weight-bold shadow-sm">Task: {{ $note->noteable ? $note->noteable->title : 'Deleted Task' }}</span>
                            @elseif($note->note_type == 3)
                                <span class="badge badge-info px-2 py-1 font-weight-bold shadow-sm">Organization: {{ $note->noteable ? $note->noteable->name : 'Deleted Organization' }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="editor-container" class="font-weight-bold text-gray-700">Description <span class="text-danger">*</span></label>
                        <input type="hidden" name="description" id="hidden-description">
                        <div id="editor-container" style="height: 350px;">{!! $note->description !!}</div>
                    </div>

                    <div class="text-right">
                        <a href="{{ request()->query('redirect_back', route('notes.index')) }}" class="btn btn-secondary mr-2">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="fas fa-save mr-1"></i> Update Note
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
    });
</script>
@endpush
