@extends('layouts.admin')

@section('title', 'System Issues')



@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">System Issues & Feedback</h1>
        <p class="text-muted mb-0 small">Track bugs, feature requests, and improvements reported in the system.</p>
    </div>
    <button type="button" class="btn btn-primary shadow-sm px-4 font-weight-bold" data-toggle="modal" data-target="#reportIssueModal">
        <i class="fas fa-plus fa-sm text-white-50 mr-2"></i> Report New Issue
    </button>
</div>

<!-- Error Alert -->
@if(isset($error) && $error)
    <div class="alert alert-warning border-left-warning shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle fa-lg mr-3 text-warning"></i>
            <div>
                <strong>Warning:</strong> {{ $error }}
            </div>
        </div>
    </div>
@endif

<!-- State Filter Tabs -->
<div class="mb-4">
    <ul class="nav nav-tabs" id="issuesFilterTab">
        <li class="nav-item">
            <a class="nav-link {{ $state == 'all' ? 'active' : '' }}" href="{{ route('issues.index', ['state' => 'all']) }}">
                <i class="fas fa-list mr-2"></i>All Issues
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $state == 'open' ? 'active' : '' }}" href="{{ route('issues.index', ['state' => 'open']) }}">
                <i class="fas fa-dot-circle mr-2 text-success"></i>Open Issues
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $state == 'closed' ? 'active' : '' }}" href="{{ route('issues.index', ['state' => 'closed']) }}">
                <i class="fas fa-check-circle mr-2 text-purple" style="color: #6f42c1;"></i>Closed Issues
            </a>
        </li>
    </ul>
</div>

<!-- Issues List -->
<div class="row">
    <div class="col-12">
        @forelse($issues as $issue)
            @php
                $priority = 'medium';
                $category = 'other';
                foreach($issue['labels'] as $label) {
                    $name = strtolower($label['name']);
                    if(in_array($name, ['low', 'medium', 'high', 'critical'])) {
                        $priority = $name;
                    }
                    if(in_array($name, ['bug', 'feature', 'improvement', 'security', 'other'])) {
                        $category = $name;
                    }
                }
            @endphp
            <div class="card shadow-sm mb-3 issue-card priority-{{ $priority }}">
                <div class="card-body p-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                        <div class="mb-2 mb-md-0" style="flex: 1;">
                            <div class="d-flex align-items-center flex-wrap mb-1">
                                <span class="text-xs font-weight-bold text-gray-500 mr-2">#{{ $issue['number'] }}</span>
                                <h5 class="h6 font-weight-bold text-gray-900 mb-0 mr-3">{{ $issue['title'] }}</h5>
                                
                                <!-- Status Badge -->
                                @if($issue['state'] == 'open')
                                    <span class="badge badge-success px-2 py-1 mr-2 text-xs">
                                        <i class="fas fa-dot-circle mr-1"></i> Open
                                    </span>
                                @else
                                    <span class="badge badge-secondary px-2 py-1 mr-2 text-xs" style="background-color: #6f42c1; color: white;">
                                        <i class="fas fa-check-circle mr-1"></i> Closed
                                    </span>
                                @endif

                                <!-- Category Badge -->
                                @if($category == 'bug')
                                    <span class="badge badge-danger px-2 py-1 mr-2 text-xs"><i class="fas fa-bug mr-1"></i> Bug</span>
                                @elseif($category == 'feature')
                                    <span class="badge badge-primary px-2 py-1 mr-2 text-xs"><i class="fas fa-lightbulb mr-1"></i> Feature</span>
                                @elseif($category == 'improvement')
                                    <span class="badge badge-info px-2 py-1 mr-2 text-xs"><i class="fas fa-rocket mr-1"></i> Improvement</span>
                                @elseif($category == 'security')
                                    <span class="badge badge-dark px-2 py-1 mr-2 text-xs"><i class="fas fa-shield-alt mr-1"></i> Security</span>
                                @else
                                    <span class="badge badge-secondary px-2 py-1 mr-2 text-xs"><i class="fas fa-question mr-1"></i> Other</span>
                                @endif

                                <!-- Priority Badge -->
                                @if($priority == 'low')
                                    <span class="badge badge-light border text-success px-2 py-1 text-xs"><i class="fas fa-circle mr-1 text-success"></i> Low</span>
                                @elseif($priority == 'medium')
                                    <span class="badge badge-light border text-warning px-2 py-1 text-xs"><i class="fas fa-circle mr-1 text-warning"></i> Medium</span>
                                @elseif($priority == 'high')
                                    <span class="badge badge-light border text-danger px-2 py-1 text-xs"><i class="fas fa-circle mr-1 text-danger"></i> High</span>
                                @elseif($priority == 'critical')
                                    <span class="badge badge-danger px-2 py-1 text-xs shadow-sm"><i class="fas fa-exclamation-triangle mr-1"></i> Critical</span>
                                @endif
                            </div>

                            <div class="text-xs text-gray-500 font-weight-bold">
                                Reported by <strong>{{ $issue['user']['login'] }}</strong> &bull;
                                Created {{ \Carbon\Carbon::parse($issue['created_at'])->diffForHumans() }}
                                @if(isset($issue['comments']) && $issue['comments'] > 0)
                                    &bull; <i class="far fa-comment mr-1"></i> {{ $issue['comments'] }} {{ Str::plural('comment', $issue['comments']) }}
                                @endif
                            </div>
                        </div>

                        <div class="d-flex align-items-center" style="gap: 8px;">
                            <button class="btn btn-outline-secondary btn-sm font-weight-bold" type="button" data-toggle="collapse" data-target="#issue-desc-{{ $issue['number'] }}" aria-expanded="false">
                                <i class="fas fa-eye mr-1"></i> View Details
                            </button>
                            <a href="{{ $issue['html_url'] }}" class="btn btn-outline-primary btn-sm font-weight-bold">
                                <i class="fas fa-tasks mr-1"></i> View Task <i class="fas fa-arrow-right fa-xs ml-1"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Collapsible Description Content -->
                    <div class="collapse mt-3" id="issue-desc-{{ $issue['number'] }}">
                        <div class="issue-description-box p-3 text-gray-800 text-sm font-weight-normal">
                            @if(trim($issue['body']))
                                {!! $issue['body'] !!}
                            @else
                                <span class="text-muted italic">No description provided.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5 bg-white rounded shadow-sm">
                <div class="text-center mb-3">
                    <i class="fas fa-check-circle fa-3x text-gray-300"></i>
                </div>
                <h5 class="font-weight-bold text-gray-700">No issues found</h5>
                <p class="text-muted">There are currently no active issues matching your filter.</p>
                <button type="button" class="btn btn-primary btn-sm px-4 mt-2" data-toggle="modal" data-target="#reportIssueModal">
                    <i class="fas fa-plus fa-sm mr-2"></i>Report An Issue
                </button>
            </div>
        @endforelse
    </div>
</div>
@endsection
