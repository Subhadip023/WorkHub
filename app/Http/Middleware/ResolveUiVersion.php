<?php

namespace App\Http\Middleware;

use App\Http\Controllers\V2\ActivityLogController as V2ActivityLogController;
use App\Http\Controllers\V2\CompanyController as V2CompanyController;
use App\Http\Controllers\V2\DashboardController as V2DashboardController;
use App\Http\Controllers\V2\IssueController as V2IssueController;
use App\Http\Controllers\V2\NoteController as V2NoteController;
use App\Http\Controllers\V2\PermissionController as V2PermissionController;
use App\Http\Controllers\V2\ProjectController as V2ProjectController;
use App\Http\Controllers\V2\RouteExplorerController as V2RouteExplorerController;
use App\Http\Controllers\V2\SettingsController as V2SettingsController;
use App\Http\Controllers\V2\TaskController as V2TaskController;
use App\Http\Controllers\V2\TeamController as V2TeamController;
use App\Http\Controllers\V2\TrashController as V2TrashController;
use Closure;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveUiVersion
{
    /**
     * Map of route names to their corresponding V2 controllers.
     *
     * @var array<string, array{class-string, string}>
     */
    protected array $v2RouteMap = [
        'dashboard' => [V2DashboardController::class, 'index'],
        'dashboard.org' => [V2DashboardController::class, 'index'],
        'projects.index' => [V2ProjectController::class, 'index'],
        'projects.show' => [V2ProjectController::class, 'show'],
        'tasks.index' => [V2TaskController::class, 'index'],
        'tasks.show' => [V2TaskController::class, 'show'],
        'notes.index' => [V2NoteController::class, 'index'],
        'notes.show' => [V2NoteController::class, 'show'],
        'companies.index' => [V2CompanyController::class, 'index'],
        'companies.show' => [V2CompanyController::class, 'show'],
        'users.index' => [V2TeamController::class, 'index'],
        'team.index' => [V2TeamController::class, 'index'],
        'issues.index' => [V2IssueController::class, 'index'],
        'activity-logs.index' => [V2ActivityLogController::class, 'index'],
        'trash.index' => [V2TrashController::class, 'index'],
        'permissions.index' => [V2PermissionController::class, 'index'],
        'profile.edit' => [V2SettingsController::class, 'index'],
        'settings.index' => [V2SettingsController::class, 'index'],
        'routes.index' => [V2RouteExplorerController::class, '__invoke'],
        'routes' => [V2RouteExplorerController::class, '__invoke'],
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Runtime switch parameter: ?ui_version=v2 or ?ui_version=v1
        if ($request->has('ui_version')) {
            $version = $request->query('ui_version');
            if (in_array($version, ['v1', 'v2'], true)) {
                session(['ui_version' => $version]);
                if ($user = $request->user()) {
                    $user->forceFill(['ui_version' => $version])->save();
                }
            }
        }

        // Only intercept safe GET/HEAD requests for authenticated users who have V2 enabled
        if (! $request->isMethodSafe() || ! $request->user() || ! $request->user()->isV2()) {
            return $next($request);
        }

        // If the URL already explicitly targets the /v2/* prefix, let it proceed normally
        if ($request->is('v2/*')) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName && isset($this->v2RouteMap[$routeName])) {
            [$controllerClass, $method] = $this->v2RouteMap[$routeName];
            $controller = app($controllerClass);

            $parameters = $request->route()->parameters();
            if (! isset($parameters['company'])) {
                $parameters['company'] = null;
            }

            $response = app()->call([$controller, $method], $parameters);

            if ($response instanceof Responsable) {
                return $response->toResponse($request);
            }

            return $response instanceof Response ? $response : response($response);
        }

        return $next($request);
    }
}
