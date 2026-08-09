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

    // 2. Alert Banner Auto-Close (Only for auto-dismiss alerts, not static UI banners)
    setTimeout(() => {
        $('.alert-auto-dismiss').alert('close');
    }, 4000);

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

    // 6. Global Ctrl+S / Cmd+S Keyboard Shortcut to Save Active Task / Form
    $(document).on('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S')) {
            e.preventDefault();

            // 1. If Edit Task Modal is open
            if ($('#editTaskModal').hasClass('show') || $('#editTaskModal').is(':visible')) {
                var $editForm = $('#editTaskForm');
                if ($editForm.length) {
                    var $submitBtn = $editForm.find('button[type="submit"], input[type="submit"]').first();
                    if ($submitBtn.length) {
                        $submitBtn.click();
                    } else {
                        $editForm.submit();
                    }
                    return;
                }
            }

            // 2. If user is currently focused inside any form input or editable area
            var activeEl = document.activeElement;
            if (activeEl && ($(activeEl).closest('form').length || $(activeEl).closest('#editor-container, .ql-editor').length)) {
                var $closestForm = $(activeEl).closest('form');
                if (!$closestForm.length && $(activeEl).closest('#editor-container, .ql-editor').length) {
                    $closestForm = $('#description-form');
                }
                if ($closestForm.length && !$closestForm.hasClass('delete-task-form') && !$closestForm.hasClass('toggle-task-form')) {
                    var $submitBtn = $closestForm.find('button[type="submit"], input[type="submit"]').first();
                    if ($submitBtn.length) {
                        $submitBtn.click();
                    } else {
                        $closestForm.submit();
                    }
                    return;
                }
            }

            // 3. If Inline Add Task row is open / visible
            if ($('#inlineAddRow').is(':visible')) {
                var $inlineForm = $('#inlineAddTaskForm');
                if ($inlineForm.length) {
                    var titleVal = $('#inline_title').val();
                    if (!titleVal || titleVal.trim() === '') {
                        $('#inline_title').focus();
                        return;
                    }
                    var $inlineSubmitBtn = $('button[form="inlineAddTaskForm"][type="submit"]');
                    if ($inlineSubmitBtn.length) {
                        $inlineSubmitBtn.click();
                    } else {
                        $inlineForm.submit();
                    }
                    return;
                }
            }

            // 4. Fallback: Any visible modal with a form
            var $openModal = $('.modal.show, .modal:visible').first();
            if ($openModal.length) {
                var $modalForm = $openModal.find('form').first();
                if ($modalForm.length) {
                    var $modalSubmitBtn = $modalForm.find('button[type="submit"], input[type="submit"]').first();
                    if ($modalSubmitBtn.length) {
                        $modalSubmitBtn.click();
                    } else {
                        $modalForm.submit();
                    }
                    return;
                }
            }
        }
    });
});

/* ============================================================
 * Notion-style Priority Badge Dropdown — Global Handler
 * Works on any page that renders <x-priority-badge>
 * ============================================================ */
(function () {
    // Inject CSS once
    if (!document.getElementById('notion-priority-styles')) {
        var style = document.createElement('style');
        style.id = 'notion-priority-styles';
        style.textContent = `
            .notion-priority-dropdown {
                position: absolute;
                z-index: 9999;
                left: 0;
                top: calc(100% + 6px);
                min-width: 170px;
                background: #ffffff;
                border-radius: 8px;
                box-shadow: 0 8px 24px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.08);
                padding: 6px 0 8px;
                animation: notionDropIn 0.12s ease;
            }
            @keyframes notionDropIn {
                from { opacity: 0; transform: translateY(-4px); }
                to   { opacity: 1; transform: translateY(0); }
            }
            .notion-dropdown-label {
                padding: 5px 12px 6px;
                color: #a0aec0;
                font-size: 10.5px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.7px;
            }
            .notion-priority-option {
                padding: 5px 10px;
                cursor: pointer;
                border-radius: 4px;
                margin: 0 4px;
                transition: background 0.1s;
                display: flex;
                align-items: center;
            }
            .notion-priority-option:hover { background: #f1f5f9; }
            .notion-priority-option .badge { font-size: 0.78rem; letter-spacing: 0.2px; }
            .priority-badge-display:hover .badge { opacity: 0.85; }
        `;
        document.head.appendChild(style);
    }

    var priorityMap = {
        '1': '<span class="badge badge-secondary px-2 py-1 shadow-sm"><i class="fas fa-arrow-down mr-1"></i> Low</span>',
        '2': '<span class="badge badge-info px-2 py-1 shadow-sm font-weight-bold"><i class="fas fa-minus mr-1"></i> Medium</span>',
        '3': '<span class="badge badge-warning text-dark px-2 py-1 shadow-sm font-weight-bold"><i class="fas fa-arrow-up mr-1"></i> High</span>',
        '4': '<span class="badge badge-danger px-2 py-1 shadow-sm font-weight-bold"><i class="fas fa-fire mr-1"></i> Urgent</span>',
    };

    function closeAllNotionDropdowns(except) {
        document.querySelectorAll('.notion-priority-dropdown').forEach(function (d) {
            if (d !== except) d.style.display = 'none';
        });
    }

    // Badge click → open/close dropdown
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.priority-cell')) {
            closeAllNotionDropdowns();
            return;
        }
        var badge = e.target.closest('.priority-badge-display');
        if (!badge) return;
        var cell = badge.closest('.priority-cell');
        if (!cell) return;
        var dropdown = cell.querySelector('.notion-priority-dropdown');
        if (!dropdown) return;
        var isOpen = dropdown.style.display !== 'none';
        closeAllNotionDropdowns();
        dropdown.style.display = isOpen ? 'none' : 'block';
    });

    // Option click → AJAX update + badge re-render
    document.addEventListener('click', function (e) {
        var option = e.target.closest('.notion-priority-option');
        if (!option) return;
        var dropdown = option.closest('.notion-priority-dropdown');
        if (!dropdown) return;
        var taskId = dropdown.getAttribute('data-task-id');
        var value  = option.getAttribute('data-value');
        var csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content;

        dropdown.style.display = 'none';

        // Optimistic UI update
        var cell = dropdown.closest('.priority-cell');
        if (cell) {
            var badgeDisplay = cell.querySelector('.priority-badge-display');
            if (badgeDisplay) badgeDisplay.innerHTML = priorityMap[value] || '';
        }

        fetch('/tasks/' + taskId, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ priority: value })
        })
        .then(function (r) { return r.ok ? r.json() : r.json().then(function (e) { throw e; }); })
        .then(function (data) {
            if (typeof showToast === 'function') showToast(data.message || 'Priority updated', 'success');
        })
        .catch(function (err) {
            if (typeof showToast === 'function') showToast((err && err.message) || 'Failed to update priority', 'error');
        });
    });
})();

/* ============================================================
 * Notion-style Due Date Picker
 * ============================================================ */
(function () {
    if (!document.getElementById('notion-date-styles')) {
        var style = document.createElement('style');
        style.id = 'notion-date-styles';
        style.textContent = `
            .notion-date-dropdown {
                position: absolute;
                z-index: 9999;
                left: 0;
                top: calc(100% + 6px);
                min-width: 190px;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 8px 24px rgba(0,0,0,.12), 0 0 0 1px rgba(0,0,0,.08);
                padding: 10px;
                border: 1px solid #e3e6f0;
            }
            .notion-date-dropdown.notion-date-dropdown-up { top: auto; bottom: calc(100% + 6px); }
            .notion-date-dropdown .notion-date-input { border-color: #d1d3e2; border-radius: 6px; }
            .notion-date-dropdown .notion-date-clear { width: 100%; padding: 5px 0 !important; border-top: 1px solid #eaecf4; text-align: left; }
        `;
        document.head.appendChild(style);
    }

    function closeAllDatePickers(except) {
        document.querySelectorAll('.notion-date-dropdown').forEach(function (dropdown) {
            if (dropdown !== except) dropdown.style.display = 'none';
        });
    }

    function badgeState(value) {
        if (!value) return { badge: 'badge-light border text-muted', icon: 'far fa-calendar-plus', label: 'No due date' };

        var date = new Date(value + 'T00:00:00');
        var today = new Date();
        today.setHours(0, 0, 0, 0);

        if (date < today) {
            return { badge: 'badge-danger', icon: 'fas fa-exclamation-triangle', label: 'Overdue (' + date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) + ')' };
        }
        if (date.getTime() === today.getTime()) {
            return { badge: 'badge-warning text-dark', icon: 'fas fa-clock', label: 'Due Today' };
        }
        return { badge: 'badge-light border text-gray-800', icon: 'far fa-calendar-alt text-primary', label: date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) };
    }

    function updateDueDate(dropdown, value) {
        var cell = dropdown.closest('.notion-date-cell');
        var state = badgeState(value);

        dropdown.style.display = 'none';
        if (cell) {
            var badge = cell.querySelector('.notion-date-badge');
            if (badge) {
                badge.className = 'badge ' + state.badge + ' px-2 py-1 shadow-sm font-weight-bold notion-date-badge';
                badge.innerHTML = '<i class="' + state.icon + ' mr-1"></i>' + state.label;
            }

            var hiddenInput = cell.querySelector('input.notion-date-hidden-input');
            if (hiddenInput) {
                hiddenInput.value = value;
                hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    }

    document.addEventListener('click', function (event) {
        var display = event.target.closest('.notion-date-display');
        if (display) {
            var cell = display.closest('.notion-date-cell');
            var dropdown = cell && cell.querySelector('.notion-date-dropdown');
            if (!dropdown) return;

            var isOpen = dropdown.style.display !== 'none';
            closeAllDatePickers();
            if (isOpen) {
                dropdown.style.display = 'none';
                return;
            }

            dropdown.classList.remove('notion-date-dropdown-up');
            dropdown.style.visibility = 'hidden';
            dropdown.style.display = 'block';
            if (window.innerHeight - display.getBoundingClientRect().bottom < dropdown.offsetHeight + 6) {
                dropdown.classList.add('notion-date-dropdown-up');
            }
            dropdown.style.visibility = '';
            return;
        }

        var clear = event.target.closest('.notion-date-clear');
        if (clear) {
            var clearDropdown = clear.closest('.notion-date-dropdown');
            clearDropdown.querySelector('.notion-date-input').value = '';
            updateDueDate(clearDropdown, '');
            return;
        }

        if (!event.target.closest('.notion-date-cell')) closeAllDatePickers();
    });

    document.addEventListener('change', function (event) {
        if (!event.target.matches('.notion-date-input')) return;
        updateDueDate(event.target.closest('.notion-date-dropdown'), event.target.value);
    });
})();

/* ============================================================
 * Notion-style Generic Select Dropdown — Global Handler
 * Works on any page that renders <x-notion-select>
 * ============================================================ */
(function () {
    // Inject CSS once (shared with priority badge)
    if (!document.getElementById('notion-select-styles')) {
        var style = document.createElement('style');
        style.id = 'notion-select-styles';
        style.textContent = `
            .notion-select-dropdown {
                position: absolute;
                z-index: 9999;
                left: 0;
                top: calc(100% + 6px);
                min-width: 180px;
                background: #ffffff;
                border-radius: 8px;
                box-shadow: 0 8px 24px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.08);
                padding: 6px;
                animation: notionDropIn 0.12s ease;
            }
            .notion-select-option {
                padding: 4px;
                cursor: pointer;
                border-radius: 4px;
                transition: background 0.1s;
                display: flex;
                align-items: center;
            }
            .notion-select-option:hover { background: #f1f5f9; }
            .notion-select-dropdown.notion-select-dropdown-up { top: auto; bottom: calc(100% + 6px); }
            .notion-select-option .badge { width: 100%; font-size: 0.78rem; letter-spacing: 0.2px; text-align: left; }
            .notion-select-display, .notion-date-display { display: inline-flex; }
            .notion-select-display:hover .badge, .notion-date-display:hover .badge { opacity: 0.85; }
            .notion-select-cell, .notion-date-cell { position: relative; min-width: 118px; white-space: nowrap; }
            .notion-current-badge, .notion-date-badge { display: inline-flex; align-items: center; min-height: 30px; border-radius: 6px; letter-spacing: 0.1px; }
            .notion-select-avatar { width: 20px; height: 20px; border-radius: 50%; object-fit: cover; vertical-align: middle; }
            .notion-select-avatar-initials { display: inline-flex; align-items: center; justify-content: center; background: #4e73df; color: #fff; font-size: 0.65rem; line-height: 1; }
        `;
        document.head.appendChild(style);
    }

    function closeAllNotionSelects(except) {
        document.querySelectorAll('.notion-select-dropdown').forEach(function (d) {
            if (d !== except) d.style.display = 'none';
        });
    }

    // Badge click → open/close dropdown
    document.addEventListener('click', function (e) {
        // Close on outside click
        if (!e.target.closest('.notion-select-cell') && !e.target.closest('.priority-cell')) {
            closeAllNotionSelects();
            return;
        }
        var display = e.target.closest('.notion-select-display');
        if (!display) return;
        var cell = display.closest('.notion-select-cell');
        if (!cell) return;
        var dropdown = cell.querySelector('.notion-select-dropdown');
        if (!dropdown) return;
        var isOpen = dropdown.style.display !== 'none';
        closeAllNotionSelects();
        if (isOpen) {
            dropdown.style.display = 'none';
            dropdown.classList.remove('notion-select-dropdown-up');
            return;
        }

        // Open upward when the menu would run past the bottom of the viewport.
        dropdown.classList.remove('notion-select-dropdown-up');
        dropdown.style.visibility = 'hidden';
        dropdown.style.display = 'block';
        var displayBottom = display.getBoundingClientRect().bottom;
        if (window.innerHeight - displayBottom < dropdown.offsetHeight + 6) {
            dropdown.classList.add('notion-select-dropdown-up');
        }
        dropdown.style.visibility = '';
    });

    // Option click → UI update + hidden input update + JS event dispatch + optional task AJAX update
    document.addEventListener('click', function (e) {
        var option = e.target.closest('.notion-select-option');
        if (!option) return;
        var dropdown = option.closest('.notion-select-dropdown');
        if (!dropdown) return;
        var taskId  = dropdown.getAttribute('data-task-id');
        var field   = dropdown.getAttribute('data-field');
        var value   = option.getAttribute('data-value');
        var badge   = option.getAttribute('data-badge');
        var icon    = option.getAttribute('data-icon');
        var avatar  = option.getAttribute('data-avatar');
        var initials = option.getAttribute('data-initials');
        var background = option.getAttribute('data-background');
        var label   = option.getAttribute('data-label');
        var csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content;

        dropdown.style.display = 'none';

        // Optimistic UI — update the displayed badge
        var cell = dropdown.closest('.notion-select-cell');
        if (cell) {
            var currentBadge = cell.querySelector('.notion-current-badge');
            if (currentBadge) {
                currentBadge.className = 'badge ' + badge + ' px-2 py-1 shadow-sm font-weight-bold notion-current-badge';
                currentBadge.style.backgroundColor = background || '';
                var avatarMarkup = avatar
                    ? '<img src="' + avatar + '" alt="" class="notion-select-avatar mr-1">'
                    : (initials ? '<span class="notion-select-avatar notion-select-avatar-initials mr-1">' + initials + '</span>' : '');
                currentBadge.innerHTML = avatarMarkup + (avatarMarkup ? '' : (icon ? '<i class="' + icon + ' mr-1"></i>' : '')) + ' ' + label;
            }

            // Update hidden input if present (for form submission)
            var hiddenInput = cell.querySelector('input.notion-select-input');
            if (hiddenInput) {
                hiddenInput.value = value;
                hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        var display = cell ? cell.querySelector('.notion-select-display') : null;
        var eventData = { value: value, label: label, field: field, taskId: taskId, badge: badge, icon: icon, avatar: avatar, initials: initials, background: background };

        if (display) {
            // Dispatch standard change event and custom notion-select:change event on display container
            display.dispatchEvent(new CustomEvent('change', { detail: eventData, bubbles: true }));
            display.dispatchEvent(new CustomEvent('notion-select:change', { detail: eventData, bubbles: true }));

            // Trigger inline data-onchange callback if defined
            var onchangeAttr = display.getAttribute('data-onchange');
            if (onchangeAttr) {
                if (typeof window[onchangeAttr] === 'function') {
                    window[onchangeAttr](value, label, eventData, display);
                } else {
                    try {
                        new Function('value', 'label', 'detail', 'element', onchangeAttr).call(display, value, label, eventData, display);
                    } catch (err) {
                        console.error('Error executing notion-select onchange handler:', err);
                    }
                }
            }
        }

        // Keep task-table completion styling in sync for status changes.
        if (field === 'status') {
            var row = dropdown.closest('tr');
            if (row) {
                var isCompleted = value === '3';
                row.classList.toggle('completed-task', isCompleted);
                row.classList.toggle('pending-task', !isCompleted);

                var titleLink = row.querySelector('.task-title-link');
                if (titleLink) {
                    titleLink.classList.toggle('text-muted', isCompleted);
                    titleLink.classList.toggle('text-line-through', isCompleted);
                }

                var toggleIcon = row.querySelector('.toggle-task-form i');
                if (toggleIcon) {
                    toggleIcon.className = isCompleted
                        ? 'far fa-check-square fa-lg text-success'
                        : 'far fa-square fa-lg text-gray-400 hover-text-success';
                }
            }
        }

    });
})();
