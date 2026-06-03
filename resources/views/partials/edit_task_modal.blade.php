{{-- Edit Task Modal --}}
<div class="modal fade" id="editTaskModal" tabindex="-1" role="dialog" aria-labelledby="editTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-info font-weight-bold" id="editTaskModalLabel">Edit Task</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form action="" method="POST" id="editTaskForm">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_task_title" class="font-weight-bold text-gray-700">Task Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_task_title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_task_description" class="font-weight-bold text-gray-700">Description</label>
                        <textarea class="form-control" id="edit_task_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="edit_task_status" class="font-weight-bold text-gray-700">Status <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit_task_status" name="status" required>
                                <option value="1">To Do</option>
                                <option value="2">In Progress</option>
                                <option value="3">Completed</option>
                                <option value="4">On Hold</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="edit_task_priority" class="font-weight-bold text-gray-700">Priority <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit_task_priority" name="priority" required>
                                <option value="1">Low</option>
                                <option value="2">Medium</option>
                                <option value="3">High</option>
                                <option value="4">Urgent</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="edit_task_type" class="font-weight-bold text-gray-700">Type <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit_task_type" name="type" required>
                                <option value="1">Task</option>
                                <option value="2">Bug</option>
                                <option value="3">Feature</option>
                                <option value="4">Improvement</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_task_assigned_to" class="font-weight-bold text-gray-700">Assign To</label>
                        <select class="form-control" id="edit_task_assigned_to" name="assigned_to">
                            <option value="">-- Unassigned --</option>
                            @foreach($companyUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_task_due_date" class="font-weight-bold text-gray-700">Due Date</label>
                        <input type="date" class="form-control" id="edit_task_due_date" name="due_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

