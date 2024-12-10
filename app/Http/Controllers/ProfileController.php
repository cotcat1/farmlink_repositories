<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
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
    
        
        
        // farm_name,farm_addressを更新
        $user->farm_name = $request->input('farm_name');
        $user->farm_address = $request->input('farm_address');

        $user->fill($request->validated());
    
        if ($user->isDirty('email')) {
            $user->email;
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

    public function uploadIcon(Request $request)
    {
        $request->validate([
            'icon' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // バリデーション
        ]);

        $user = Auth::user();

        // 既存のアイコンを削除
        if ($user->icon) {
            Storage::disk('public')->delete($user->icon);
        }

        // 新しいアイコンを保存
        $iconPath = $request->file('icon')->store('icons', 'public');
        $user->icon = $iconPath;
        $user->save();

        return response()->json([
            'success' => true,
            'path' => asset('storage/' . $iconPath),
        ]);
    }
}
