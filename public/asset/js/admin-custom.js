/**
 * Custom Admin layout functionality for WorkHub
 */

$(document).ready(function() {
    // 1. Toast Notification Auto-Dismiss
    setTimeout(function() {
        $('.toast-notification').fadeTo(500, 0).slideUp(500, function(){
            $(this).remove(); 
        });
    }, 4000);

    // 2. Alert Banner Auto-Close
    setTimeout(() => {
        $('.alert').alert('close');
    }, 3000);

    // 3. Sidebar Responsive Behavior
    // Ensure sidebar is closed on initial load for mobile
    if ($(window).width() < 768) {
        $("body").removeClass("sidebar-toggled");
        $(".sidebar").removeClass("toggled");
    }

    // Close sidebar when clicking on backdrop or mobile close button
    $(document).on('click', '.sidebar-backdrop, #sidebarCloseMobile', function() {
        $("body").removeClass("sidebar-toggled");
        $(".sidebar").removeClass("toggled");
        if (typeof $.fn.collapse === 'function') {
            $('.sidebar .collapse').collapse('hide');
        }
    });

    // Handle window resizing to clean up state
    $(window).resize(function() {
        if ($(window).width() >= 768) {
            $("body").removeClass("sidebar-toggled");
        }
    });

    // 4. Universal Delete Confirmation Modal
    var formToSubmit = null;

    $(document).on('submit', 'form', function(e) {
        var action = $(this).attr('action');
        var method = $(this).find('input[name="_method"]').val() || $(this).attr('method');
        
        if (action && (method === 'DELETE' || method === 'delete')) {
            var isTask = action.indexOf('/tasks/') !== -1;
            var isProject = action.indexOf('/projects/') !== -1;
            var isCompany = action.indexOf('/companies/') !== -1;

            var isTaskPrune = isTask && action.indexOf('/tasks/images/') === -1 && action.indexOf('/tasks/import') === -1;
            var isProjectPrune = isProject;
            var isCompanyPrune = isCompany && action.indexOf('/members/') === -1 && action.indexOf('/reject-request/') === -1 && action.indexOf('/leave') === -1;

            if (isTaskPrune || isProjectPrune || isCompanyPrune) {
                if (formToSubmit === this) {
                    formToSubmit = null;
                    return true;
                }

                e.preventDefault();
                formToSubmit = this;

                var type = '';
                var message = 'This item will be moved to the Trash Bin for 30 days, after which it will be deleted permanently.';

                if (isTaskPrune) {
                    type = 'Task';
                    message = 'This task will be moved to the Trash Bin for 30 days, after which it will be deleted permanently.';
                } else if (isProjectPrune) {
                    type = 'Project';
                    message = 'This project will be moved to the Trash Bin for 30 days, after which it and all its tasks will be deleted permanently.';
                } else if (isCompanyPrune) {
                    type = 'Organization';
                    message = 'This organization will be moved to the Trash Bin for 30 days, after which it and all its projects, tasks, and members will be deleted permanently.';
                }

                $('#deleteConfirmItemType').text(type);
                $('#deleteConfirmMessage').text(message);
                $('#deleteConfirmModal').modal('show');
            }
        }
    });

    $('#confirmDeleteSubmitBtn').click(function() {
        if (formToSubmit) {
            formToSubmit.submit();
        }
        $('#deleteConfirmModal').modal('hide');
    });

    // 5. Notifications Handling
    if (window.AppRoutes && window.AppRoutes.notifications) {
        function fetchNotifications() {
            $.ajax({
                url: window.AppRoutes.notifications.index,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    renderNotifications(response.notifications);
                },
                error: function(xhr, status, error) {
                    console.error("Failed to fetch notifications:", error);
                }
            });
        }

        function renderNotifications(notifications) {
            var container = $('#alertsDropdownContainer');
            var counter = $('#alertsCounter');
            var markAllBtn = $('#markAllReadBtn');

            container.empty();

            if (!notifications || notifications.length === 0) {
                counter.hide().text('0');
                markAllBtn.hide();
                container.append('<div class="dropdown-item text-center small text-gray-500 py-3">No new notifications</div>');
                return;
            }

            // Show counter and mark all button
            counter.text(notifications.length).show();
            markAllBtn.show();

            notifications.forEach(function(notif) {
                var iconClass = 'fa-bell';
                var iconBgClass = 'bg-primary';

                if (notif.type === 'project_created') {
                    iconClass = 'fa-folder-plus';
                    iconBgClass = 'bg-primary';
                } else if (notif.type === 'task_created') {
                    iconClass = 'fa-tasks';
                    iconBgClass = 'bg-success';
                } else if (notif.type === 'task_assigned') {
                    iconClass = 'fa-user-check';
                    iconBgClass = 'bg-info';
                } else if (notif.type === 'task_status_updated') {
                    iconClass = 'fa-check-circle';
                    iconBgClass = 'bg-info';
                } else if (notif.type === 'task_priority_updated') {
                    iconClass = 'fa-exclamation-circle';
                    iconBgClass = 'bg-warning';
                } else if (notif.type === 'task_deadline_updated') {
                    iconClass = 'fa-calendar-alt';
                    iconBgClass = 'bg-warning';
                } else if (notif.type === 'task_deleted') {
                    iconClass = 'fa-trash-alt';
                    iconBgClass = 'bg-danger';
                } else if (notif.type === 'join_request') {
                    iconClass = 'fa-user-clock';
                    iconBgClass = 'bg-warning';
                } else if (notif.type === 'join_approved') {
                    iconClass = 'fa-check-circle';
                    iconBgClass = 'bg-success';
                } else if (notif.type === 'join_rejected') {
                    iconClass = 'fa-times-circle';
                    iconBgClass = 'bg-danger';
                }

                var dateStr = new Date(notif.created_at).toLocaleString();

                var itemHtml = `
                    <a class="dropdown-item d-flex align-items-center notification-item" href="${notif.data?.url || '#'}" data-id="${notif.id}">
                        <div class="mr-3">
                            <div class="icon-circle ${iconBgClass}">
                                <i class="fas ${iconClass} text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500">${dateStr}</div>
                            <span class="font-weight-bold text-gray-800">${notif.title}</span>
                            <div class="text-gray-600 small">${notif.message}</div>
                        </div>
                    </a>
                `;
                container.append(itemHtml);
            });

            // Click handler to mark individual notification as read
            $('.notification-item').off('click').on('click', function(e) {
                e.preventDefault();
                var notifId = $(this).data('id');
                var item = $(this);

                $.ajax({
                    url: `/notifications/${notifId}/read`,
                    type: "PATCH",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function() {
                        item.fadeOut(300, function() {
                            item.remove();
                            fetchNotifications();
                        });
                        window.location.href = item.attr('href');
                    },
                    error: function(xhr, status, error) {
                        console.error("Failed to mark notification as read:", error);
                    }
                });
            });
        }

        // Mark all as read click handler
        $('#markAllReadBtn').off('click').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // prevent closing dropdown instantly
            $.ajax({
                url: window.AppRoutes.notifications.readAll,
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {
                    fetchNotifications();
                },
                error: function(xhr, status, error) {
                    console.error("Failed to mark all notifications as read:", error);
                }
            });
        });

        // Fetch immediately on load
        fetchNotifications();

        // Poll every 30 seconds (30000ms)
        setInterval(fetchNotifications, 30000);
    }
});
