<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UiSwitchController extends Controller
{
    /**
     * Switch user's preferred UI version (v1 or v2).
     */
    public function __invoke(Request $request, string $version)
    {
        if (! in_array($version, ['v1', 'v2'], true)) {
            $version = 'v2';
        }

        session(['ui_version' => $version]);

        if ($user = $request->user()) {
            $user->forceFill(['ui_version' => $version])->save();
        }

        $label = $version === 'v2' ? 'Modern V2' : 'Classic V1';

        $referer = $request->header('referer');
        if ($referer && str_starts_with($referer, url('/'))) {
            // Strip any explicit /v2/ from referer so user transitions to clean URL
            $cleanReferer = str_replace(url('/v2'), url(''), $referer);

            return redirect($cleanReferer)->with('success', "Switched interface to {$label}.");
        }

        return redirect()->route('dashboard')->with('success', "Switched interface to {$label}.");
    }
}
