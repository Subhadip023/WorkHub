<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IssueController extends Controller
{
    /**
     * Display a listing of existing issues.
     */
    public function index(Request $request)
    {
        $state = $request->query('state', 'all');
        if (! in_array($state, ['open', 'closed', 'all'])) {
            $state = 'all';
        }

        $pat = config('services.github.pat');
        $owner = config('services.github.owner');
        $repo = config('services.github.repo');

        if (empty($pat)) {
            return view('issues.index', [
                'issues' => collect(),
                'error' => 'GitHub Personal Access Token (PAT) is not configured in the server environment (.env).',
                'state' => $state,
                'owner' => $owner,
                'repo' => $repo,
            ]);
        }

        // Send GET request to GitHub API
        $response = Http::withHeaders([
            'Accept' => 'application/vnd.github+json',
            'Authorization' => "Bearer {$pat}",
            'X-GitHub-Api-Version' => '2022-11-28',
        ])
            ->withUserAgent('WorkHub-App')
            ->get("https://api.github.com/repos/{$owner}/{$repo}/issues", [
                'state' => $state,
                'per_page' => 100,
            ]);

        $issues = collect();
        $error = null;

        if ($response->successful()) {
            $issues = collect($response->json())->filter(function ($issue) {
                return ! isset($issue['pull_request']);
            });
        } else {
            $error = 'Failed to fetch issues from GitHub: '.($response->json('message') ?: 'Unknown error');
            Log::error('GitHub Issue list fetch failed', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
        }

        return view('issues.index', [
            'issues' => $issues,
            'error' => $error,
            'state' => $state,
            'owner' => $owner,
            'repo' => $repo,
        ]);
    }

    /**
     * Submit an issue report to GitHub.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'priority' => 'required|string|in:low,medium,high,critical',
            'category' => 'required|string|in:bug,feature,improvement,security,other',
            'description' => 'required|string',
            'attachment' => 'nullable|file|max:10240', // 10MB limit
        ]);

        $pat = config('services.github.pat');
        $owner = config('services.github.owner');
        $repo = config('services.github.repo');

        if (empty($pat)) {
            return response()->json([
                'success' => false,
                'message' => 'GitHub Personal Access Token (PAT) is not configured in the server environment (.env).',
            ], 500);
        }

        $user = auth()->user();

        // Handle attachment if uploaded
        $attachmentUrl = null;
        $attachmentName = null;
        if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $path = $file->store('issues', 'public');
            $attachmentUrl = asset('storage/'.$path);
        }

        // // Build Markdown Body for GitHub Issue
        $body = '';
        // $body = "### 📋 Issue Details\n\n";
        // $body .= "* **Reporter:** {$user->name} ({$user->email})\n";
        // $body .= '* **Category:** '.ucfirst($request->input('category'))."\n";
        // $body .= '* **Priority:** '.ucfirst($request->input('priority'))."\n";

        if ($attachmentUrl) {
            $extension = strtolower(pathinfo($attachmentName, PATHINFO_EXTENSION));
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

            if (in_array($extension, $imageExtensions)) {
                $body .= "* **Attachment:**\n\n![{$attachmentName}]({$attachmentUrl})\n";
            } else {
                $body .= "* **Attachment:** [{$attachmentName}]({$attachmentUrl})\n";
            }
        }

        $body .= "\n### 📝 Description\n\n".$request->input('description')."\n\n";
        $body .= "---\n*Reported via WorkHub Issue Form*";
        // add issue user name and email in issue body
        $body .= "\n### 👤 Reporter\n\n {$user->name} \n Email : {$user->email}";

        // Map category and priority to labels
        $labels = [$request->input('category'), $request->input('priority')];

        // Send POST request to GitHub API
        $response = Http::withHeaders([
            'Accept' => 'application/vnd.github+json',
            'Authorization' => "Bearer {$pat}",
            'X-GitHub-Api-Version' => '2022-11-28',
        ])
            ->withUserAgent('WorkHub-App')
            ->post("https://api.github.com/repos/{$owner}/{$repo}/issues", [
                'title' => $request->input('title'),
                'body' => $body,
                'labels' => $labels,
            ]);

        if ($response->successful()) {
            $issueUrl = $response->json('html_url');

            return response()->json([
                'success' => true,
                'message' => 'Issue successfully created on GitHub.',
                'url' => $issueUrl,
            ]);
        }

        Log::error('GitHub Issue submission failed', [
            'status' => $response->status(),
            'response' => $response->body(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to submit the issue to GitHub. Please check server logs.',
        ], 502);
    }
}
