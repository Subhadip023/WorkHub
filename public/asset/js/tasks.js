$(document).ready(function() {
    // 1. Edit Task Modal Populating
    $(document).on('click', '.edit-task-btn', function() {
        var id = $(this).data('id');
        var title = $(this).data('title');
        var description = $(this).data('description');
        var due_date = $(this).data('due_date');
        var assigned_to = $(this).data('assigned_to');
        var status = $(this).data('status');
        var priority = $(this).data('priority');
        var type = $(this).data('type');
        var action = $(this).data('action');

        $('#editTaskForm').attr('action', action);
        $('#edit_task_title').val(title);
        $('#edit_task_description').val(description);
        $('#edit_task_due_date').val(due_date);
        $('#edit_task_assigned_to').val(assigned_to);
        $('#edit_task_status').val(status);
        $('#edit_task_priority').val(priority);
        $('#edit_task_type').val(type);
    });

    // 2. Inline Task Addition Visibility
    $('#btnShowInlineAdd').click(function(e) {
        e.preventDefault();
        $('#noTasksContainer').hide();
        $('#tasksTableContainer').show();
        $('#inlineAddRow').show();
        $('#inline_title').focus();
    });

    // 3. Cancel Inline Add
    $('#cancelInlineAdd').click(function() {
        $('#inlineAddRow').hide();
        
        if (typeof window.applyFilter === 'function') {
            window.applyFilter();
        } else {
            // If there are no other tasks, restore the "No tasks found" state
            var taskCount = $('#tasksTable tbody tr').length - 1; // subtract 1 for the inline row itself
            if (taskCount <= 0) {
                $('#tasksTableContainer').hide();
                $('#noTasksContainer').show();
            }
        }

        // Get default auth user ID from data attribute
        var defaultUserId = $('#inlineAddRow').data('user-id') || '';

        // Clear values
        $('#inlineAddRow input').val('');
        $('#inlineAddRow select').val('');
        
        // Set default values back
        $('#inlineAddRow select[name="type"]').val('1');
        $('#inlineAddRow select[name="assigned_to"]').val(defaultUserId);
        $('#inlineAddRow select[name="status"]').val('1');
        $('#inlineAddRow select[name="priority"]').val('2');
    });

    // 4. Submit Inline Form on Enter Key
    $(document).on('keypress', '#inline_title', function(e) {
        if (e.key === 'Enter' || e.which === 13) {
            e.preventDefault();
            $('#inlineAddTaskForm').submit();
        }
    });
});
