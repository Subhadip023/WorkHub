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
    <link href="{{ asset('asset/css/admin-custom.css') }}" rel="stylesheet">

    <!-- Global App Routes and variables for JavaScript -->
    <script>
        window.AppRoutes = {
            notifications: {
                index: "{{ route('notifications.index') }}",
                readAll: "{{ route('notifications.readAll') }}"
            }
        };
    </script>
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
    <script src="{{ asset('asset/js/admin-custom.js') }}"></script>

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

    <!-- Universal Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header bg-danger text-white p-4">
                    <h5 class="modal-title font-weight-bold d-flex align-items-center" id="deleteConfirmModalLabel">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Confirm Move to Trash
                    </h5>
                    <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-gray-800 font-weight-bold mb-2">Are you sure you want to delete this <span id="deleteConfirmItemType">item</span>?</p>
                    <p class="text-gray-600 mb-0" id="deleteConfirmMessage">
                        This item will be moved to the Trash Bin for 30 days, after which it will be deleted permanently.
                    </p>
                </div>
                <div class="modal-footer bg-light p-3 border-top-0">
                    <button class="btn btn-secondary font-weight-bold" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger font-weight-bold" type="button" id="confirmDeleteSubmitBtn">Move to Trash</button>
                </div>
            </div>
        </div>
    </div>



    @stack('scripts')
</body>

</html>

