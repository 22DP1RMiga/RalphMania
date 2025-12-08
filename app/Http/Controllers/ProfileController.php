<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Display the password change form.
     */
    public function passwordEdit(Request $request): Response
    {
        return Inertia::render('Profile/Password', [
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's password.
     */
    public function passwordUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return Redirect::route('profile.edit')->with('status', 'password-updated');
    }

    /**
     * Update the user's avatar.
     */
    public function avatarUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'max:2048'], // 2MB max
        ]);

        $user = $request->user();

        // Delete old avatar if exists
        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        // Store new avatar
        $path = $request->file('photo')->store('avatars', 'public');

        $user->update([
            'profile_picture' => $path,
        ]);

        return Redirect::back()->with('status', 'avatar-updated');
    }

    /**
     * Delete the user's avatar.
     */
    public function avatarDelete(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $user->update([
            'profile_picture' => null,
        ]);

        return Redirect::route('profile.edit')->with('status', 'avatar-deleted');
    }

    /**
     * Display the user's addresses.
     */
    public function addresses(Request $request): Response
    {
        return Inertia::render('Profile/Addresses', [
            'addresses' => $request->user()->addresses,
        ]);
    }

    /**
     * Show the form for creating a new address.
     */
    public function addressCreate(): Response
    {
        return Inertia::render('Profile/AddressCreate');
    }

    /**
     * Store a newly created address.
     */
    public function addressStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state_province' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'is_default' => ['boolean'],
        ]);

        $user = $request->user();

        // If this is set as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create($validated);

        return Redirect::route('profile.addresses')->with('status', 'address-created');
    }

    /**
     * Show the form for editing an address.
     */
    public function addressEdit($id): Response
    {
        $address = Auth::user()->addresses()->findOrFail($id);

        return Inertia::render('Profile/AddressEdit', [
            'address' => $address,
        ]);
    }

    /**
     * Update an address.
     */
    public function addressUpdate(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate([
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state_province' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'is_default' => ['boolean'],
        ]);

        $user = $request->user();
        $address = $user->addresses()->findOrFail($id);

        // If this is set as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            $user->addresses()->where('id', '!=', $id)->update(['is_default' => false]);
        }

        $address->update($validated);

        return Redirect::route('profile.addresses')->with('status', 'address-updated');
    }

    /**
     * Delete an address.
     */
    public function addressDelete($id): RedirectResponse
    {
        $address = Auth::user()->addresses()->findOrFail($id);
        $address->delete();

        return Redirect::route('profile.addresses')->with('status', 'address-deleted');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
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
