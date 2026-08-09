{{-- Create Subtask Modal --}}
@if(isset($canMutate) ? $canMutate : auth()->user()->can('update', $task))
    <div class="modal fade" id="addSubtaskModal" tabindex="-1" role="dialog" aria-labelledby="addSubtaskModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="addSubtaskModalLabel">
                        <i class="fas fa-plus-circle mr-2"></i> Create Subtask
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('tasks.subtasks.store', $task) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="form-row mb-3">
                            <div class="col">
                                <label for="subtask_title" class="font-weight-bold text-xs text-uppercase text-gray-700 mb-1 d-block">Subtask Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="subtask_title" class="form-control form-control-lg font-weight-bold text-gray-900" placeholder="Enter subtask title..." required>
                            </div>
                            <div class="col-auto">
                                <label for="subtask_points" class="font-weight-bold text-xs text-uppercase text-gray-700 mb-1 d-block">Points</label>
                                <input type="number" name="points" id="subtask_points" class="form-control form-control-lg font-weight-bold text-gray-900" style="width: 110px;" min="0" max="99999" placeholder="Pts">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="font-weight-bold text-xs text-uppercase text-gray-700 mb-2 d-block">Assignee</label>
                                <x-assignee-badge
                                    name="assigned_to"
                                    :assigned-to="auth()->id()"
                                    :users="$companyUsers ?? []"
                                    :editable="true"
                                    wrapper="div"
                                />
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label class="font-weight-bold text-xs text-uppercase text-gray-700 mb-2 d-block">Due Date</label>
                                <x-due-date-badge
                                    name="due_date"
                                    :due-date="null"
                                    :editable="true"
                                    wrapper="div"
                                />
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="font-weight-bold text-xs text-uppercase text-gray-700 mb-2 d-block">Priority</label>
                                <x-priority-badge
                                    name="priority"
                                    :priority="2"
                                    :editable="true"
                                    wrapper="div"
                                />
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label class="font-weight-bold text-xs text-uppercase text-gray-700 mb-2 d-block">Type</label>
                                <x-task-type-badge
                                    name="type"
                                    :task-type="1"
                                    :editable="true"
                                    wrapper="div"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light px-4 py-3">
                        <button type="button" class="btn btn-sm btn-secondary font-weight-bold px-3" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary font-weight-bold shadow-sm px-4">
                            <i class="fas fa-check mr-1"></i> Add Subtask
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
