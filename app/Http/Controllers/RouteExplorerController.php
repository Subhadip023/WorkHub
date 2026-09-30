<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;

class RouteExplorerController extends Controller
{
    /**
     * Display the all routes explorer page in the modern New Design layout.
     */
    public function __invoke(Request $request)
    {
        $user = auth()->user() ?? [
            'id' => 1,
            'name' => 'WorkHub Developer',
            'email' => 'dev@workhub.io',
            'role' => 'Administrator',
            'profile_image' => null,
        ];

        $routes = collect(Route::getRoutes())->map(function (LaravelRoute $route) {
            $rawUri = $route->uri();
            $uri = '/'.ltrim($rawUri, '/');
            $methods = array_values(array_filter($route->methods(), fn ($m) => $m !== 'HEAD'));
            $name = $route->getName() ?: '';
            $action = $route->getActionName();
            $middleware = array_values($route->gatherMiddleware());

            // Extract concise action
            if ($action === 'Closure') {
                $actionDisplay = 'Closure';
                $controllerName = 'Closure';
                $methodName = 'invoke';
            } elseif (str_contains($action, '@')) {
                $parts = explode('@', $action);
                $controllerName = class_basename($parts[0]);
                $methodName = $parts[1] ?? 'index';
                $actionDisplay = "{$controllerName}@{$methodName}";
            } else {
                $controllerName = class_basename($action);
                $methodName = '__invoke';
                $actionDisplay = $controllerName;
            }

            // Categorize route
            $category = $this->determineCategory($uri, $name);

            // Determine rendering engine
            $engine = $this->determineEngine($uri, $name, $action, $middleware);

            // Extract parameter names
            preg_match_all('/\{([a-zA-Z0-9_?]+)\}/', $uri, $matches);
            $parameters = $matches[1];
            $hasParameters = ! empty($parameters);

            // Generate workable sample URL for testing
            $sampleUrl = $this->generateSampleUrl($uri);

            // Middleware flags
            $requiresAuth = in_array('auth', $middleware) || in_array('verified', $middleware);
            $isGuest = in_array('guest', $middleware);
            $isAdmin = in_array('can:manage-features', $middleware);
            $isApi = in_array('api', $middleware) || str_starts_with($uri, '/api');

            // Can be directly previewed/visited in browser
            $canVisit = in_array('GET', $methods) && ! str_starts_with($uri, '/storage') && ! str_contains($uri, 'devtools');

            return [
                'uri' => $uri,
                'raw_uri' => $rawUri,
                'methods' => $methods,
                'name' => $name,
                'action' => $actionDisplay,
                'full_action' => $action,
                'controller' => $controllerName,
                'method_name' => $methodName,
                'middleware' => $middleware,
                'category' => $category,
                'engine' => $engine,
                'parameters' => $parameters,
                'has_parameters' => $hasParameters,
                'sample_url' => $sampleUrl,
                'requires_auth' => $requiresAuth,
                'is_guest' => $isGuest,
                'is_admin' => $isAdmin,
                'is_api' => $isApi,
                'can_visit' => $canVisit,
            ];
        })
        // Filter out purely internal debugging/vendor endpoints if any
            ->filter(function ($item) {
                return ! Str::startsWith($item['uri'], '/_ignition') && ! Str::startsWith($item['uri'], '/sanctum');
            })
            ->values();

        // Calculate summary metrics
        $total = $routes->count();
        $getCount = $routes->filter(fn ($r) => in_array('GET', $r['methods']))->count();
        $postCount = $routes->filter(fn ($r) => in_array('POST', $r['methods']))->count();
        $mutationCount = $routes->filter(fn ($r) => count(array_intersect(['POST', 'PUT', 'PATCH', 'DELETE'], $r['methods'])) > 0)->count();
        $inertiaCount = $routes->filter(fn ($r) => $r['engine'] === 'Inertia React')->count();
        $bladeCount = $routes->filter(fn ($r) => $r['engine'] === 'Blade View')->count();
        $apiCount = $routes->filter(fn ($r) => $r['engine'] === 'API / JSON')->count();
        $protectedCount = $routes->filter(fn ($r) => $r['requires_auth'])->count();
        $publicCount = $total - $protectedCount;

        $stats = [
            'total' => $total,
            'get_count' => $getCount,
            'post_count' => $postCount,
            'mutation_count' => $mutationCount,
            'inertia_count' => $inertiaCount,
            'blade_count' => $bladeCount,
            'api_count' => $apiCount,
            'protected_count' => $protectedCount,
            'public_count' => $publicCount,
        ];

        // Categories with individual counts
        $categories = $routes->groupBy('category')->map(function ($items, $category) {
            return [
                'name' => $category,
                'count' => $items->count(),
                'get_count' => $items->filter(fn ($r) => in_array('GET', $r['methods']))->count(),
            ];
        })->values()->sortByDesc('count')->values()->all();

        return Inertia::render('New/Routes', [
            'user' => $user,
            'routes' => $routes,
            'stats' => $stats,
            'categories' => $categories,
        ]);
    }

    /**
     * Assign a semantic category to each route based on URI and Name.
     */
    protected function determineCategory(string $uri, string $name): string
    {
        if (str_starts_with($uri, '/api') || str_contains($uri, 'external-api')) {
            return 'API & Integrations';
        }

        if (str_starts_with($uri, '/tasks') || str_starts_with($uri, '/new/task') || str_starts_with($uri, '/comments')) {
            return 'Tasks';
        }

        if (str_starts_with($uri, '/projects') || $uri === '/new/projects') {
            return 'Projects';
        }

        if (str_starts_with($uri, '/companies') || str_contains($uri, 'personal/switch') || str_starts_with($uri, '/invitations') || $uri === '/new/companies' || $uri === '/new/team') {
            return 'Organizations & Teams';
        }

        if (str_starts_with($uri, '/notes') || $uri === '/new/notes') {
            return 'Notes & Docs';
        }

        if (str_starts_with($uri, '/issues') || $uri === '/new/issues') {
            return 'Issues Tracker';
        }

        if (str_starts_with($uri, '/admin') || str_starts_with($uri, '/permissions') || $uri === '/new/permissions') {
            return 'Administration';
        }

        if (str_starts_with($uri, '/activity-logs') || str_starts_with($uri, '/trash') || str_starts_with($uri, '/notifications') || str_starts_with($uri, '/search') || str_starts_with($uri, '/log-viewer')) {
            return 'System & Logs';
        }

        if (
            str_starts_with($uri, '/login') ||
            str_starts_with($uri, '/register') ||
            str_starts_with($uri, '/logout') ||
            str_starts_with($uri, '/profile') ||
            str_starts_with($uri, '/password') ||
            str_starts_with($uri, '/reset-password') ||
            str_starts_with($uri, '/forgot-password') ||
            str_starts_with($uri, '/confirm-password') ||
            str_starts_with($uri, '/verify-email') ||
            str_starts_with($uri, '/email/') ||
            $uri === '/new/settings'
        ) {
            return 'Auth & Profile';
        }

        if (
            $uri === '/' ||
            str_starts_with($uri, '/dashboard') ||
            str_starts_with($uri, '/analytics') ||
            str_starts_with($uri, '/old-dashboard') ||
            str_starts_with($uri, '/demo') ||
            str_starts_with($uri, '/new/dashboard') ||
            str_starts_with($uri, '/new/analytics') ||
            str_starts_with($uri, '/routes')
        ) {
            return 'Core Suite';
        }

        return 'System & Framework';
    }

    /**
     * Determine UI engine: Inertia React vs Blade View vs API vs System.
     */
    protected function determineEngine(string $uri, string $name, string $action, array $middleware): string
    {
        if (str_starts_with($uri, '/api') || str_contains($uri, 'external-api')) {
            return 'API / JSON';
        }

        if (
            $uri === '/dashboard' ||
            str_starts_with($uri, '/dashboard/') ||
            $uri === '/analytics' ||
            str_starts_with($uri, '/projects') ||
            $uri === '/tasks' ||
            str_starts_with($uri, '/tasks/') ||
            str_starts_with($uri, '/new/') ||
            str_starts_with($uri, '/demo/') ||
            $uri === '/routes' ||
            str_contains($action, 'DashboardController') ||
            str_contains($action, 'AnalyticsController') ||
            str_contains($action, 'RouteExplorerController') ||
            str_contains($action, 'TaskView')
        ) {
            return 'Inertia React';
        }

        if (str_starts_with($uri, '/log-viewer') || str_starts_with($uri, '/storage') || $uri === '/up' || str_contains($uri, 'devtools')) {
            return 'System';
        }

        return 'Blade View';
    }

    /**
     * Generate an executable sample URL with mock parameters for live previewing.
     */
    protected function generateSampleUrl(string $uri): string
    {
        $replacements = [
            '{company}' => '1',
            '{project}' => '1',
            '{task}' => '1',
            '{subtask}' => '1',
            '{note}' => '1',
            '{user}' => '1',
            '{id}' => '1',
            '{invitation}' => '1',
            '{externalTaskApi}' => '1',
            '{credential}' => '1',
            '{image}' => '1',
            '{comment}' => '1',
            '{token}' => 'sample-preview-token',
            '{hash}' => 'sample-hash-12345',
            '{path}' => 'sample.pdf',
            '{view?}' => '',
        ];

        $url = str_replace(array_keys($replacements), array_values($replacements), $uri);

        return rtrim($url, '/');
    }
}
