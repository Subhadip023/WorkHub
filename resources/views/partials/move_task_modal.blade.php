{{-- Move Task Modal --}}
@php
    if (!isset($projects) || (is_countable($projects) && count($projects) === 0)) {
        $user = auth()->user();
        if ($user) {
            $companyIds = $user->companies()->pluck('company_id')->toArray();
            $projects = \App\Models\Project::select('id', 'name', 'theme')
                ->whereIn('company_id', $companyIds)
                ->orWhere(function ($query) use ($user) {
                    $query->whereNull('company_id')->where('user_id', $user->id);
                })
                ->get();
        } else {
            $projects = collect();
        }
    }
@endphp

<div class="modal fade" id="moveTaskModal" tabindex="-1" role="dialog" aria-labelledby="moveTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-weight-bold" id="moveTaskModalLabel">
                    <i class="fas fa-exchange-alt mr-2"></i>Move Task
                </h5>
                <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form action="" method="POST" id="moveTaskForm">
                @csrf
                @method('PATCH')
                <div class="modal-body p-4">
                    <p class="text-gray-700 mb-3">
                        Select a target project for <strong id="moveTaskTitle" class="text-gray-900">this task</strong>:
                    </p>
                    <div class="form-group mb-0">
                        <label for="move_task_project_id" class="font-weight-bold text-xs text-uppercase text-gray-700 mb-1 d-block">Target Project</label>
                        <select class="form-control form-control-lg font-weight-bold" id="move_task_project_id" name="project_id">
                            <option value="">Personal Space (No Project)</option>
                            @if(isset($projects) && count($projects) > 0)
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button class="btn btn-secondary btn-sm" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary btn-sm font-weight-bold shadow-xs px-3" type="submit">
                        <i class="fas fa-file-export mr-1"></i>Move Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    if (window.moveTaskModalScriptLoaded) return;
    window.moveTaskModalScriptLoaded = true;

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.move-task-btn');
        if (!btn) return;

        var title = btn.getAttribute('data-title');
        var projectId = btn.getAttribute('data-project_id');
        var action = btn.getAttribute('data-action');

        var form = document.getElementById('moveTaskForm');
        var titleElem = document.getElementById('moveTaskTitle');
        var selectElem = document.getElementById('move_task_project_id');

        if (form) form.setAttribute('action', action);
        if (titleElem) titleElem.textContent = '"' + title + '"';
        if (selectElem) selectElem.value = (projectId !== null && projectId !== '' && projectId !== 'null') ? projectId : '';

        if (typeof $ !== 'undefined' && $('#moveTaskModal').length) {
            $('#moveTaskModal').modal('show');
        }
    });
})();
</script>
