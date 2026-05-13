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
     * Parāda lietotāja profila veidlapu
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Atjaunina lietotāja profila informāciju
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
     * Parāda paroles maiņas veidlapu
     */
    public function passwordEdit(Request $request): Response
    {
        return Inertia::render('Profile/Password', [
            'status' => session('status'),
        ]);
    }

    /**
     * Atjaunina lietotāja paroli
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
     * Atjaunina lietotāja profila bildi
     */
    public function avatarUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'max:2048'], // 2MB maksimums
        ]);

        $user = $request->user();

        // Dzēš veco profila bildi, ja tāds ir
        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        // Saglabā jaunu profila bildi
        $path = $request->file('photo')->store('avatars', 'public');

        $user->update([
            'profile_picture' => $path,
        ]);

        return Redirect::back()->with('status', 'avatar-updated');
    }

    /**
     * Dzēs lietotāja profila bildi
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
     * Parāda lietotāja adreses
     */
    public function addresses(Request $request): Response
    {
        return Inertia::render('Profile/Addresses', [
            'addresses' => $request->user()->addresses,
        ]);
    }

    /**
     * Parāda veidlapu jaunas adreses izveidei
     */
    public function addressCreate(): Response
    {
        return Inertia::render('Profile/AddressCreate');
    }

    /**
     * Saglabā jaunizveidotu adresi
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

        // Ja šis ir iestatīts kā noklusējuma iestatījums, tad atceļ pārējo noklusējuma iestatījumus
        if ($validated['is_default'] ?? false) {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create($validated);

        return Redirect::route('profile.addresses')->with('status', 'address-created');
    }

    /**
     * Parāda veidlapu adreses rediģēšanai
     */
    public function addressEdit($id): Response
    {
        $address = Auth::user()->addresses()->findOrFail($id);

        return Inertia::render('Profile/AddressEdit', [
            'address' => $address,
        ]);
    }

    /**
     * Atjaunina adresi
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

        // Ja šis ir iestatīts kā noklusējuma iestatījums, tad tiek atiestatīti citi noklusējuma iestatījumi
        if ($validated['is_default'] ?? false) {
            $user->addresses()->where('id', '!=', $id)->update(['is_default' => false]);
        }

        $address->update($validated);

        return Redirect::route('profile.addresses')->with('status', 'address-updated');
    }

    /**
     * Dzēš adresi
     */
    public function addressDelete($id): RedirectResponse
    {
        $address = Auth::user()->addresses()->findOrFail($id);
        $address->delete();

        return Redirect::route('profile.addresses')->with('status', 'address-deleted');
    }

    /**
     * Dzēš lietotāja kontu
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

    public function updatePrivacy(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'is_private' => 'required|boolean'
        ]);

        // Apgriezt, lai saglabātu kā is_public
        $user->update([
            'is_public' => !$validated['is_private']
        ]);

        return response()->json([
            'message' => 'Privacy settings updated successfully'
        ]);
    }

    /**
     * Atjaunina lietotāja vēlamo valodu
     */
    public function updateLocale(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', 'in:lv,en'],
        ]);

        $request->user()->update(['locale' => $validated['locale']]);

        return response()->json(['ok' => true])
            ->header('X-Inertia', 'false');
    }
}
