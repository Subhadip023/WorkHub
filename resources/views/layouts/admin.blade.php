<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - WorkHub</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('asset/img/logo.svg') }}">

    <!-- Custom fonts for this template-->
    <link href="{{ asset('asset/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('asset/css/sb-admin-2.min.css') }}" rel="stylesheet">

    @stack('styles')
    
    <!-- Custom styling for modern responsive sidebar -->
    <style>
        /* Mobile responsive sidebar styles */
        @media (max-width: 767.98px) {
            #wrapper {
                position: relative;
                overflow-x: hidden;
            }
            
            #wrapper .sidebar {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                bottom: 0 !important;
                z-index: 1050 !important;
                width: 260px !important;
                height: 100vh !important;
                transform: translateX(-100%) !important;
                transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important;
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2) !important;
                display: flex !important;
                flex-direction: column !important;
            }
            
            #wrapper .sidebar.toggled {
                transform: translateX(0) !important;
            }
            
            /* Sidebar items styling in mobile drawer */
            .sidebar .sidebar-brand .sidebar-brand-text {
                display: inline !important;
            }
            
            .sidebar .nav-item .nav-link {
                text-align: left !important;
                width: 100% !important;
                padding: 1rem 1.5rem !important;
                display: flex !important;
                align-items: center !important;
            }
            
            .sidebar .nav-item .nav-link i {
                font-size: 0.95rem !important;
                margin-right: 0.75rem !important;
                width: 1.5rem !important;
                text-align: center !important;
            }
            
            .sidebar .nav-item .nav-link span {
                font-size: 0.9rem !important;
                display: inline !important;
            }
            
            /* Background backdrop */
            .sidebar-backdrop {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background-color: rgba(15, 23, 42, 0.6); /* Modern slate/dark backdrop */
                backdrop-filter: blur(4px);
                -webkit-backdrop-filter: blur(4px);
                z-index: 1040;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            body.sidebar-toggled .sidebar-backdrop {
                opacity: 1;
                pointer-events: auto;
            }
            
            body.sidebar-toggled {
                overflow: hidden !important;
            }
        }
        
        /* Hamburger Button Styling */
        #sidebarToggleTop {
            background-color: #f8f9fc;
            border: 1px solid #eaecf4;
            color: #4e73df;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease-in-out;
        }
        #sidebarToggleTop:hover {
            background-color: #4e73df;
            color: #fff;
            border-color: #4e73df;
        }
        
        /* Smooth transitions for toggle actions on desktop as well */
        @media (min-width: 768px) {
            .sidebar {
                transition: width 0.2s ease-in-out !important;
            }
        }
    </style>
</head>

<body id="page-top">
    <!-- Sidebar Backdrop for Mobile -->
    <div class="sidebar-backdrop"></div>
    @php
        $icons = [
            'success' => 'fa-check-circle text-success',
            'error' => 'fa-exclamation-circle text-danger',
            'warning' => 'fa-exclamation-triangle text-warning',
            'info' => 'fa-info-circle text-info'
        ];
        $borderColors = [
            'success' => '#1cc88a',
            'error' => '#e74a3b',
            'warning' => '#f6c23e',
            'info' => '#36b9cc'
        ];
    @endphp

    <div style="position: fixed; top: 25px; right: 25px; z-index: 1050; max-width: 380px; min-width: 280px;">
        @foreach (['success', 'error', 'warning', 'info'] as $type)
            @if(session($type))
                <div class="toast-notification alert alert-dismissible fade show p-3 border-0" role="alert" 
                     style="background: rgba(255, 255, 255, 0.98); 
                            backdrop-filter: blur(8px); 
                            -webkit-backdrop-filter: blur(8px); 
                            border-left: 5px solid {{ $borderColors[$type] }} !important; 
                            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15); 
                            border-radius: 8px; 
                            color: #333;
                            margin-bottom: 12px;
                            display: block;">
                    <div class="d-flex align-items-center">
                        <i class="fas {{ $icons[$type] }} fa-lg mr-3"></i>
                        <div style="flex-grow: 1; font-weight: 600; font-size: 0.9rem; padding-right: 20px;">
                            {{ session($type) }}
                        </div>
                        <button type="button" class="close p-2" data-dismiss="alert" aria-label="Close" style="top: 50%; transform: translateY(-50%); right: 5px;">
                            <span aria-hidden="true" class="text-gray-500">&times;</span>
                        </button>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    @if(session('success') || session('error') || session('warning') || session('info'))
        @push('scripts')
        <script>
            $(document).ready(function() {
                setTimeout(function() {
                    $('.toast-notification').fadeTo(500, 0).slideUp(500, function(){
                        $(this).remove(); 
                    });
                }, 4000);
            });
        </script>
        @endpush
    @endif

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        @include('partials.sidebar')
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                @include('partials.topbar')
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    @yield('content')
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            @include('partials.footer')
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="#"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('asset/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('asset/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('asset/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <!-- Custom scripts for all pages-->
    <script src="{{ asset('asset/js/sb-admin-2.min.js') }}"></script>
    <script>
        setTimeout(() => {
            $('.alert').alert('close');
        }, 3000);

        $(document).ready(function() {
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
        });
    </script>
    <script>
        $(document).ready(function() {
            function fetchNotifications() {
                $.ajax({
                    url: "{{ route('notifications.index') }}",
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
                    url: "{{ route('notifications.readAll') }}",
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
        });
    </script>

    @if(auth()->check())
        @php
            $pendingInvitations = \App\Models\CompanyInvitation::where('email', auth()->user()->email)->with('company')->get();
        @endphp
        @if($pendingInvitations->isNotEmpty())
            <!-- Workspace Invitations Modal -->
            <div class="modal fade" id="invitationsModal" tabindex="-1" role="dialog" aria-labelledby="invitationsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                        <div class="modal-header bg-gradient-primary text-white p-4">
                            <h5 class="modal-title font-weight-bold d-flex align-items-center" id="invitationsModalLabel">
                                <i class="fas fa-envelope-open-text mr-2 fa-lg"></i>
                                Workspace Invitation
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="text-gray-700">You have been invited to join the following workspace(s). Please accept or reject to proceed:</p>
                            <div class="list-group list-group-flush shadow-sm rounded border">
                                @foreach($pendingInvitations as $invite)
                                    <div class="list-group-item d-flex flex-column flex-sm-row justify-content-between align-items-sm-center p-3">
                                        <div class="mb-3 mb-sm-0">
                                            <h6 class="font-weight-bold text-gray-800 mb-1">
                                                <i class="fas fa-building text-primary mr-1"></i>
                                                {{ $invite->company->name ?? 'Organization' }}
                                            </h6>
                                            <span class="badge badge-light border text-monospace text-xs">
                                                Code: {{ $invite->company->code ?? '' }}
                                            </span>
                                        </div>
                                        <div class="d-flex">
                                            <form action="{{ route('invitations.reject', $invite) }}" method="POST" class="mr-2">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm px-3 font-weight-bold">
                                                    <i class="fas fa-times-circle mr-1"></i> Reject
                                                </button>
                                            </form>
                                            <form action="{{ route('invitations.accept', $invite) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm px-3 font-weight-bold">
                                                    <i class="fas fa-check-circle mr-1"></i> Accept
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="modal-footer bg-light p-3">
                            <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Decide Later</button>
                        </div>
                    </div>
                </div>
            </div>

            @push('scripts')
            <script>
                $(document).ready(function() {
                    $('#invitationsModal').modal({
                        backdrop: 'static',
                        keyboard: false
                    }).modal('show');
                });
            </script>
            @endpush
        @endif
    @endif

    @stack('scripts')
</body>

</html>

