<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $emailSubject }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
        }
        .wrapper {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #4f46e5, #3b82f6);
            padding: 32px 36px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            font-size: 24px;
            margin: 0;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .header p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 14px;
            margin: 6px 0 0;
        }
        .body {
            padding: 32px 36px;
            line-height: 1.6;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin: 20px 0;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #f1f5f9;
            padding: 12px;
        }
        .stat-card {
            display: table-cell;
            text-align: center;
            padding: 8px;
            width: 33.33%;
        }
        .stat-number {
            font-size: 22px;
            font-weight: 700;
            color: #4f46e5;
        }
        .stat-number.urgent {
            color: #ef4444;
        }
        .stat-label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-top: 2px;
        }
        .section-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin: 24px 0 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .task-list {
            margin: 0 0 24px 0;
            padding: 0;
            list-style: none;
        }
        .task-item {
            padding: 12px 16px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .task-header {
            font-weight: 600;
            color: #1e293b;
            font-size: 14px;
        }
        .task-meta {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-urgent { background: #fee2e2; color: #991b1b; }
        .badge-high { background: #ffedd5; color: #9a3412; }
        .badge-medium { background: #e0f2fe; color: #075985; }
        .badge-low { background: #f1f5f9; color: #475569; }
        
        .cta-container {
            text-align: center;
            margin: 32px 0 16px;
        }
        .btn-cta {
            display: inline-block;
            background: #4f46e5;
            color: #ffffff !important;
            font-weight: 600;
            font-size: 15px;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }
        .footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px 36px;
            text-align: center;
            font-size: 13px;
            color: #94a3b8;
        }
        .footer a {
            color: #4f46e5;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>{{ config('app.name', 'WorkHub') }}</h1>
            <p>Daily Digest — {{ now()->format('l, F j, Y') }}</p>
        </div>
        <div class="body">
            <div class="greeting">
                Hello {{ $recipient && $recipient->name ? $recipient->name : 'there' }},
            </div>
            
            <p>{{ $emailBody }}</p>

            <div class="stats-grid">
                <div class="stat-card" style="width: 50%;">
                    <div class="stat-number">{{ $counts['todayCount'] ?? 0 }}</div>
                    <div class="stat-label">Due Today</div>
                </div>
                <div class="stat-card" style="width: 50%;">
                    <div class="stat-number {{ ($counts['overdueCount'] ?? 0) > 0 ? 'urgent' : '' }}">{{ $counts['overdueCount'] ?? 0 }}</div>
                    <div class="stat-label">Overdue</div>
                </div>
            </div>

            @if(count($todayTasks) > 0)
                <div class="section-title">Today & Priority Tasks</div>
                <ul class="task-list">
                    @foreach($todayTasks as $task)
                        <li class="task-item">
                            <div class="task-header">
                                {{ $task->title }}
                                @if($task->priority == 4)
                                    <span class="badge badge-urgent">Urgent</span>
                                @elseif($task->priority == 3)
                                    <span class="badge badge-high">High</span>
                                @elseif($task->priority == 2)
                                    <span class="badge badge-medium">Medium</span>
                                @else
                                    <span class="badge badge-low">Low</span>
                                @endif
                            </div>
                            <div class="task-meta">
                                @if($task->project)
                                    <span>Project: {{ $task->project->name }}</span> &bull; 
                                @endif
                                <span>Due: {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d, Y') : 'No Due Date' }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            <div class="cta-container">
                <a href="{{ $dashboardUrl }}" class="btn-cta">
                    View Today Tasks on Dashboard &rarr;
                </a>
            </div>
        </div>
        <div class="footer">
            &copy; {{ now()->year }} {{ config('app.name', 'WorkHub') }}. All rights reserved.<br>
            <a href="{{ $dashboardUrl }}">Open WorkHub Dashboard</a>
        </div>
    </div>
</body>
</html>
