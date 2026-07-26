<div class="card h-100 shadow-sm border-0 note-card-hover" style="border-left: 4px solid #f6c23e; border-radius: 8px;">
    <div class="card-body p-4 d-flex flex-column">
        <div class="d-flex align-items-center justify-content-between mb-2">
            @if($note->note_type == 1)
                <span class="badge badge-primary px-2 py-1 font-weight-bold" style="background-color: rgba(78, 115, 223, 0.15); color: #4e73df;">Personal Note</span>
            @elseif($note->note_type == 2)
                <span class="badge badge-success px-2 py-1 font-weight-bold" style="background-color: rgba(28, 200, 138, 0.15); color: #1cc88a;">Project Note</span>
            @elseif($note->note_type == 3)
                <span class="badge badge-warning px-2 py-1 font-weight-bold" style="background-color: rgba(246, 194, 62, 0.15); color: #f6c23e;">Task Note</span>
            @elseif($note->note_type == 4)
                <span class="badge badge-info px-2 py-1 font-weight-bold" style="background-color: rgba(54, 185, 204, 0.15); color: #36b9cc;">Company Note</span>
            @endif
        </div>

        <h5 class="font-weight-bold mb-2">
            <a href="{{ route('notes.show', $note) }}" class="text-gray-900 text-decoration-none hover-link" style="font-size: 1.1rem;">
                {{ $note->title }}
            </a>
        </h5>

        <p class="text-gray-600 mb-3 flex-grow-1" style="font-size: 0.85rem; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; white-space: normal;">
            {!! strip_tags($note->description ?? 'No content.') !!}
        </p>

        <div class="d-flex align-items-center justify-content-between text-xs text-gray-500 font-weight-bold border-top pt-2">
            <span>
                <i class="fas fa-comments mr-1"></i>{{ $note->comments->count() }} Comments
            </span>
            <span>
                <i class="far fa-clock mr-1"></i>{{ $note->created_at->diffForHumans() }}
            </span>
        </div>
    </div>
</div>
