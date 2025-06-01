<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.index');
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:1024'],
            'telegram_chat_id' => ['nullable', 'string', 'max:255'],
            'current_password' => ['nullable', 'required_with:password', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('The current password is incorrect.');
                }
            }],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // Update basic info
        $user->name = $validated['name'];
        $user->telegram_chat_id = $validated['telegram_chat_id'];

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar_url) {
                Storage::delete($user->avatar_url);
            }

            // Store new avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_url = $path;
        }

        // Update password if provided
        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('status', 'Profile updated successfully!');
    }

    public function enableTwoFactor(Request $request)
    {
        $user = auth()->user();
        $user->two_factor_enabled = true;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Two-factor authentication enabled successfully'
        ]);
    }

    public function disableTwoFactor(Request $request)
    {
        $user = auth()->user();
        $user->two_factor_enabled = false;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Two-factor authentication disabled successfully'
        ]);
    }

    public function logoutOtherDevices(Request $request)
    {
        $request->validate([
            'password' => ['required', function ($attribute, $value, $fail) {
                if (!Hash::check($value, auth()->user()->password)) {
                    $fail('The password is incorrect.');
                }
            }],
        ]);

        auth()->logoutOtherDevices($request->password);

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out from other devices successfully'
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', function ($attribute, $value, $fail) {
                if (!Hash::check($value, auth()->user()->password)) {
                    $fail('The password is incorrect.');
                }
            }],
        ]);

        $user = auth()->user();

        // Delete avatar if exists
        if ($user->avatar_url) {
            Storage::delete($user->avatar_url);
        }

        auth()->logout();
        $user->delete();

        return redirect('/')->with('status', 'Your account has been deleted successfully.');
    }
} 