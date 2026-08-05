<!-- Discussion Drawer Overlay -->
<div id="discussionDrawerOverlay" class="discussion-drawer-overlay"></div>

<!-- Slide-Out Discussion Drawer Panel -->
<div id="discussionDrawer" class="discussion-drawer shadow-lg">
    <div class="discussion-drawer-header d-flex align-items-center justify-content-between px-3 py-3 border-bottom text-white" style="background-color: {{ $project->theme ?? '#4e73df' }};">
        <h6 class="m-0 font-weight-bold text-white d-flex align-items-center">
            <i class="fas fa-comments mr-2"></i> Project Discussion
            <span class="badge badge-light text-dark font-weight-bold ml-2">{{ count($comments ?? []) }}</span>
        </h6>
        <button type="button" class="close text-white opacity-75 hover-opacity-100" id="btnCloseDiscussionDrawer" aria-label="Close">
            <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
        </button>
    </div>
    <div class="discussion-drawer-body p-3">
        @include('partials.comments', [
            'comments' => $comments,
            'commentableType' => 'project',
            'commentableId' => $project->id
        ])
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    function openDiscussionDrawer() {
        $('#discussionDrawer').addClass('open');
        $('#discussionDrawerOverlay').addClass('show');
        localStorage.setItem('discussionDrawerOpen_{{ $project->id }}', 'true');
    }

    function closeDiscussionDrawer() {
        $('#discussionDrawer').removeClass('open');
        $('#discussionDrawerOverlay').removeClass('show');
        localStorage.setItem('discussionDrawerOpen_{{ $project->id }}', 'false');
    }

    $(document).on('click', '#btnToggleDiscussion', function(e) {
        e.preventDefault();
        if ($('#discussionDrawer').hasClass('open')) {
            closeDiscussionDrawer();
        } else {
            openDiscussionDrawer();
        }
    });

    $(document).on('click', '#btnCloseDiscussionDrawer, #discussionDrawerOverlay', function() {
        closeDiscussionDrawer();
    });

    $(document).on('keyup', function(e) {
        if (e.key === "Escape" && $('#discussionDrawer').hasClass('open')) {
            closeDiscussionDrawer();
        }
    });

    if (localStorage.getItem('discussionDrawerOpen_{{ $project->id }}') === 'true') {
        openDiscussionDrawer();
    }
});
</script>
@endpush
