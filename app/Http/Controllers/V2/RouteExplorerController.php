<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\RouteExplorerController as BaseRouteExplorerController;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RouteExplorerController extends BaseRouteExplorerController
{
    /**
     * Display the all routes explorer page in the modern New Design layout.
     */
    public function __invoke(Request $request)
    {
        return Inertia::render('New/Routes', $this->buildRouteData());
    }
}
