<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if ($request->filled('cropped_image')) {
            $base64_image = $request->input('cropped_image');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64_image, $type)) {
                $image_base64 = base64_decode(substr($base64_image, strpos($base64_image, ',') + 1));
                $image_type = strtolower($type[1]);
                $fileName = 'profile-images/' . uniqid() . '.' . $image_type;
                \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $image_base64);
                if ($user->profile_image) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_image);
                }
                $data['profile_image'] = $fileName;
            }
        } elseif ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_image);
            }
            $path = $request->file('profile_image')->store('profile-images', 'public');
            $data['profile_image'] = $path;
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
