@extends('layouts.admin')

@section('title', 'All Routes')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
            <i class="fas fa-route text-primary mr-2"></i>All Routes &amp; Endpoints
        </h1>
        <p class="text-muted small mb-0">System route registry, HTTP verbs, middleware, and rendering engines</p>
    </div>
    <div class="d-none d-sm-inline-block">
        <span class="badge badge-light border px-3 py-2 font-weight-bold text-gray-700 shadow-sm">
            <i class="fas fa-server mr-1 text-success"></i> Laravel Registry • {{ $stats['total'] }} Endpoints
        </span>
    </div>
</div>

<!-- Summary Metric Cards -->
<div class="row mb-4">
    <!-- Total Routes Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Routes</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800 font-mono">{{ $stats['total'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-network-wired fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GET Requests Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">GET Endpoints</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800 font-mono">{{ $stats['get_count'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-arrow-down fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mutations (POST / PUT / DELETE) Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Mutations (POST / PUT)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800 font-mono">{{ $stats['mutation_count'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-edit fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Protected Routes Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Auth Protected</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800 font-mono">{{ $stats['protected_count'] }} <span class="text-muted font-weight-normal text-xs">({{ $stats['public_count'] }} public)</span></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-lock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Route Search & Filters Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-white d-flex flex-wrap align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-filter mr-1"></i> Filter &amp; Search Routes
        </h6>
        <div class="text-xs text-muted mt-1 mt-sm-0">
            Showing <span id="visibleRouteCount" class="font-weight-bold text-gray-800">{{ $stats['total'] }}</span> of {{ $stats['total'] }} routes
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <!-- Search Input -->
            <div class="col-lg-6 col-md-12 mb-2 mb-lg-0">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-gray-400"></i></span>
                    </div>
                    <input type="text" id="routeSearchInput" class="form-control border-left-0 bg-light" placeholder="Search by URI, name, or controller (e.g. /tasks, TaskController)...">
                </div>
            </div>

            <!-- Method Filter -->
            <div class="col-lg-3 col-sm-6 mb-2 mb-sm-0">
                <select id="methodFilter" class="form-control custom-select bg-light">
                    <option value="">All HTTP Methods</option>
                    <option value="GET">GET Only</option>
                    <option value="POST">POST Only</option>
                    <option value="PUT">PUT Only</option>
                    <option value="PATCH">PATCH Only</option>
                    <option value="DELETE">DELETE Only</option>
                </select>
            </div>

            <!-- Engine Filter -->
            <div class="col-lg-3 col-sm-6">
                <select id="engineFilter" class="form-control custom-select bg-light">
                    <option value="">All Rendering Engines</option>
                    <option value="Blade View">Blade View ({{ $stats['blade_count'] }})</option>
                    <option value="Inertia React">Inertia React ({{ $stats['inertia_count'] }})</option>
                    <option value="API / JSON">API / JSON ({{ $stats['api_count'] }})</option>
                </select>
            </div>
        </div>

        <!-- Category Pills -->
        <div class="d-flex flex-wrap align-items-center gap-1 mt-2" id="categoryPills">
            <button type="button" class="btn btn-sm btn-primary active category-filter-btn mr-1 mb-1 font-weight-bold" data-category="">
                All ({{ $stats['total'] }})
            </button>
            @foreach($categories as $cat)
                <button type="button" class="btn btn-sm btn-outline-secondary category-filter-btn mr-1 mb-1" data-category="{{ $cat['name'] }}">
                    {{ $cat['name'] }} <span class="badge badge-light border ml-1">{{ $cat['count'] }}</span>
                </button>
            @endforeach
        </div>
    </div>
</div>

<!-- Routes Table Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-white">
        <h6 class="m-0 font-weight-bold text-gray-800">
            <i class="fas fa-list mr-1"></i> Routes Registry Table
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 text-sm" id="routesTable">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 110px;">Method</th>
                        <th>URI &amp; Route Name</th>
                        <th>Action / Handler</th>
                        <th>Category</th>
                        <th>Engine</th>
                        <th>Security</th>
                        <th class="text-right" style="width: 80px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($routes as $r)
                        <tr class="route-row"
                            data-uri="{{ strtolower($r['uri']) }}"
                            data-name="{{ strtolower($r['name']) }}"
                            data-action="{{ strtolower($r['action']) }}"
                            data-controller="{{ strtolower($r['controller']) }}"
                            data-methods="{{ implode(',', $r['methods']) }}"
                            data-category="{{ $r['category'] }}"
                            data-engine="{{ $r['engine'] }}"
                            data-auth="{{ $r['requires_auth'] ? '1' : '0' }}">
                            
                            <!-- Methods -->
                            <td>
                                @foreach($r['methods'] as $m)
                                    @php
                                        $methodClass = match($m) {
                                            'GET' => 'badge-success',
                                            'POST' => 'badge-primary',
                                            'PUT' => 'badge-warning',
                                            'PATCH' => 'badge-info',
                                            'DELETE' => 'badge-danger',
                                            default => 'badge-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $methodClass }} px-2 py-1 font-mono text-xs">{{ $m }}</span>
                                @endforeach
                            </td>

                            <!-- URI & Name -->
                            <td>
                                <div class="font-mono text-xs font-weight-bold text-gray-900 mb-0">
                                    {{ $r['uri'] }}
                                </div>
                                @if($r['name'])
                                    <div class="text-[11px] text-muted font-mono mt-0.5">
                                        <i class="fas fa-tag mr-1 text-gray-400"></i>{{ $r['name'] }}
                                    </div>
                                @endif
                            </td>

                            <!-- Action / Controller -->
                            <td>
                                <div class="text-xs font-weight-bold text-gray-800">{{ $r['action'] }}</div>
                                <div class="text-[11px] text-muted font-mono">{{ $r['controller'] }}</div>
                            </td>

                            <!-- Category -->
                            <td>
                                <span class="badge badge-light border text-gray-700 px-2 py-1 text-xs">
                                    {{ $r['category'] }}
                                </span>
                            </td>

                            <!-- Engine -->
                            <td>
                                @if($r['engine'] === 'Inertia React')
                                    <span class="badge badge-dark text-xs px-2 py-1">Inertia React</span>
                                @elseif($r['engine'] === 'Blade View')
                                    <span class="badge badge-light border text-primary font-weight-bold text-xs px-2 py-1">Blade View</span>
                                @elseif($r['engine'] === 'API / JSON')
                                    <span class="badge badge-secondary text-xs px-2 py-1">API / JSON</span>
                                @else
                                    <span class="badge badge-light border text-xs px-2 py-1">{{ $r['engine'] }}</span>
                                @endif
                            </td>

                            <!-- Security / Middleware -->
                            <td>
                                @if($r['is_admin'])
                                    <span class="badge badge-warning text-dark px-2 py-1 text-xs" title="Super Admin Only">
                                        <i class="fas fa-user-shield mr-1"></i>Admin
                                    </span>
                                @elseif($r['requires_auth'])
                                    <span class="badge badge-dark px-2 py-1 text-xs" title="Requires Authenticated Session">
                                        <i class="fas fa-lock mr-1 text-warning"></i>Auth
                                    </span>
                                @elseif($r['is_guest'])
                                    <span class="badge badge-secondary px-2 py-1 text-xs" title="Guest Only">Guest</span>
                                @else
                                    <span class="badge badge-light border text-gray-600 px-2 py-1 text-xs">Public</span>
                                @endif
                            </td>

                            <!-- Action Visit Button -->
                            <td class="text-right">
                                @if($r['can_visit'])
                                    <a href="{{ $r['sample_url'] }}" target="_blank" class="btn btn-sm btn-outline-primary shadow-sm" title="Open {{ $r['sample_url'] }} in new tab">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- No Matching Results Message -->
        <div id="noRoutesMessage" class="p-5 text-center text-muted" style="display: none;">
            <i class="fas fa-search fa-3x text-gray-300 mb-3"></i>
            <h5 class="font-weight-bold text-gray-700">No matching routes found</h5>
            <p class="small mb-0">Try clearing or adjusting your search keyword, category, or HTTP method filter.</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('routeSearchInput');
        const methodFilter = document.getElementById('methodFilter');
        const engineFilter = document.getElementById('engineFilter');
        const categoryButtons = document.querySelectorAll('.category-filter-btn');
        const rows = document.querySelectorAll('.route-row');
        const countSpan = document.getElementById('visibleRouteCount');
        const emptyMsg = document.getElementById('noRoutesMessage');

        let activeCategory = '';

        function filterRoutes() {
            const query = (searchInput.value || '').toLowerCase().trim();
            const method = (methodFilter.value || '').trim();
            const engine = (engineFilter.value || '').trim();

            let visibleCount = 0;

            rows.forEach(row => {
                const uri = row.getAttribute('data-uri') || '';
                const name = row.getAttribute('data-name') || '';
                const action = row.getAttribute('data-action') || '';
                const controller = row.getAttribute('data-controller') || '';
                const rowMethods = (row.getAttribute('data-methods') || '').split(',');
                const rowCategory = row.getAttribute('data-category') || '';
                const rowEngine = row.getAttribute('data-engine') || '';

                // Text search match
                const matchesText = !query || uri.includes(query) || name.includes(query) || action.includes(query) || controller.includes(query);

                // Method match
                const matchesMethod = !method || rowMethods.includes(method);

                // Category match
                const matchesCategory = !activeCategory || rowCategory === activeCategory;

                // Engine match
                const matchesEngine = !engine || rowEngine === engine;

                if (matchesText && matchesMethod && matchesCategory && matchesEngine) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (countSpan) {
                countSpan.textContent = visibleCount;
            }

            if (emptyMsg) {
                emptyMsg.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        }

        searchInput.addEventListener('input', filterRoutes);
        methodFilter.addEventListener('change', filterRoutes);
        engineFilter.addEventListener('change', filterRoutes);

        categoryButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                categoryButtons.forEach(b => {
                    b.classList.remove('btn-primary', 'active');
                    b.classList.add('btn-outline-secondary');
                });
                this.classList.remove('btn-outline-secondary');
                this.classList.add('btn-primary', 'active');

                activeCategory = this.getAttribute('data-category') || '';
                filterRoutes();
            });
        });
    });
</script>
@endpush
@endsection

