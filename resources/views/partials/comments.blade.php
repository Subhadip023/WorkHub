<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Discussion Thread</h6>
        <span class="badge badge-primary">{{ $comments->count() }} Comments</span>
    </div>
    <div class="card-body">
        <!-- Comment Feed -->
        <div class="comment-feed mb-4" style="max-height: 400px; overflow-y: auto; padding-right: 5px;">
            @forelse($comments as $comment)
                <div class="media mb-3 pb-3 border-bottom">
                    <div class="mr-3 rounded-circle d-flex align-items-center justify-content-center text-white font-weight-bold" 
                         style="width: 38px; height: 38px; background: linear-gradient(135deg, #4e73df, #224abe); font-size: 0.9rem; flex-shrink: 0;">
                        {{ strtoupper(substr($comment->user->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="media-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold text-gray-800" style="font-size: 0.9rem;">
                                {{ $comment->user->name ?? 'Unknown User' }}
                            </span>
                            <div class="d-flex align-items-center">
                                <span class="text-xs text-muted mr-2">
                                    {{ $comment->created_at->diffForHumans() }}
                                </span>
                                @if($comment->user_id === auth()->id())
                                    <form action="{{ route('comments.destroy', $comment) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this comment?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0 m-0 align-baseline" style="font-size: 0.8rem;" title="Delete Comment">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        <div class="text-gray-700 mt-1 small" style="white-space: pre-line; line-height: 1.4;">{{ $comment->content }}</div>
                    </div>
                </div>
            @empty
                <div class="text-center py-4">
                    <div class="text-gray-400 mb-2" style="font-size: 1.5rem;"><i class="far fa-comments"></i></div>
                    <p class="text-muted small mb-0 font-italic">No comments yet. Start the conversation!</p>
                </div>
            @endforelse
        </div>

        <!-- Comment Input Form -->
        <form action="{{ route('comments.store') }}" method="POST" class="mt-3">
            @csrf
            <input type="hidden" name="commentable_type" value="{{ $commentableType }}">
            <input type="hidden" name="commentable_id" value="{{ $commentableId }}">
            
            <div class="form-group mb-2">
                <textarea name="content" class="form-control form-control-sm" rows="3" placeholder="Write a comment..." required style="resize: none; border-radius: 8px; font-size: 0.875rem; border: 1px solid #d1d3e2;"></textarea>
            </div>
            <div class="text-right">
                <button type="submit" class="btn btn-primary btn-sm px-3 font-weight-bold" style="border-radius: 8px;">
                    Post Comment
                </button>
            </div>
        </form>
    </div>
</div>
