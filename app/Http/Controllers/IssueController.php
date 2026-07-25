<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IssueController extends Controller
{
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
            $path = $request->file('attachment')->store('issues', 'public');
            $attachmentUrl = asset('storage/'.$path);
            $attachmentName = $request->file('attachment')->getClientOriginalName();
        }

        // Build Markdown Body for GitHub Issue
        $body = "### 📋 Issue Details\n\n";
        $body .= "* **Reporter:** {$user->name} ({$user->email})\n";
        $body .= '* **Category:** '.ucfirst($request->input('category'))."\n";
        $body .= '* **Priority:** '.ucfirst($request->input('priority'))."\n";

        if ($attachmentUrl) {
            $body .= "* **Attachment:** [{$attachmentName}]({$attachmentUrl})\n";
        }

        $body .= "\n### 📝 Description\n\n".$request->input('description')."\n\n";
        $body .= "---\n*Reported via WorkHub Issue Form*";

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
