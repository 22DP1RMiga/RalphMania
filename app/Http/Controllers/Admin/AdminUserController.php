<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    /**
     * Parāda lietotāju sarakstu administratoram
     */
    public function index(Request $request)
    {
        $query = User::with('role');

        // Meklēšanai
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filtrē pēc lomām
        if ($request->filled('role')) {
            $query->whereHas('role', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Filtrē pēc statusiem
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'active':
                    $query->where('is_active', true);
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
                case 'verified':
                    $query->whereNotNull('email_verified_at');
                    break;
                case 'unverified':
                    $query->whereNull('email_verified_at');
                    break;
            }
        }

        // Kārtošanai
        $query->orderBy('created_at', 'desc');

        // Lappusēm (for pagination)
        $users = $query->paginate(20)->through(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone' => $user->phone,
                'profile_picture' => $user->profile_picture,
                'is_active' => $user->is_active,
                'email_verified_at' => $user->email_verified_at,
                'last_login_at' => $user->last_login_at,
                'created_at' => $user->created_at,
                'role' => $user->role ? [
                    'id' => $user->role->id,
                    'name' => $user->role->name,
                ] : null,
            ];
        });

        // Iegūst lomas filtra nolaižamajā izvēlnē (dropdown)
        $roles = Role::orderBy('name')->get(['id', 'name']);

        // Iegūst statistiku
        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
        ];

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $request->only(['search', 'role', 'status']),
            'stats' => $stats,
        ]);
    }

    /**
     * Parāda norādīto lietotāju
     */
    public function show($id)
    {
        $user = User::with(['role', 'orders', 'reviews', 'comments'])->findOrFail($id);

        // Veido adresi no lietotāja laukiem (pa cik adreses tiek glabātas lietotāju tabulā)
        $address = null;
        if ($user->address || $user->city || $user->country) {
            $address = [
                'address' => $user->address,
                'city' => $user->city,
                'country' => $user->country,
                'postal_code' => $user->postal_code,
            ];
        }

        // Informācija par biļetenu abonentiem
        $subscriber = NewsletterSubscriber::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        $newsletterStatus = null;
        if ($subscriber) {
            $newsletterStatus = [
                'subscribed'    => $subscriber->is_active && !$subscriber->is_expired,
                'is_active'     => $subscriber->is_active,
                'is_verified'   => $subscriber->is_verified,
                'expires_at'    => $subscriber->subscription_expires_at?->format('d.m.Y'),
                'days_remaining'=> $subscriber->days_remaining,
                'is_expired'    => $subscriber->is_expired,
                'preferences'   => [
                    'receive_news'          => $subscriber->receive_news,
                    'receive_promotions'    => $subscriber->receive_promotions,
                    'receive_announcements' => $subscriber->receive_announcements,
                ],
            ];
        }

        return Inertia::render('Admin/Users/Show', [
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone' => $user->phone,
                'birth_date' => $user->birth_date,
                'country' => $user->country,
                'city' => $user->city,
                'address' => $user->address,
                'postal_code' => $user->postal_code,
                'profile_picture' => $user->profile_picture,
                'is_active' => $user->is_active,
                'email_verified_at' => $user->email_verified_at,
                'last_login_at' => $user->last_login_at,
                'created_at' => $user->created_at,
                'role' => $user->role,
                'orders_count' => $user->orders->count(),
                'reviews_count' => $user->reviews->count(),
                'comments_count' => $user->comments->count(),
                'total_spent' => $user->orders->where('status', '!=', 'cancelled')->sum('total_amount'),
                // Return address as array with single item for backwards compatibility
                'addresses' => $address ? [$address] : [],
            ],
            'newsletterStatus' => $newsletterStatus,
        ]);
    }

    /**
     * Parāda lietotāja rediģēšanas veidlapu
     */
    public function edit($id)
    {
        $user = User::with('role')->findOrFail($id);
        $roles = Role::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    /**
     * Atjaunina norādīto lietotāju
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username,' . $id,
            'email' => 'required|email|max:100|unique:users,email,' . $id,
            'first_name' => 'nullable|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Lietotājs veiksmīgi atjaunināts!');
    }

    /**
     * Pārslēdz lietotāja aktīvo statusu
     */
    public function toggleActive($id)
    {
        $user = User::findOrFail($id);

        // Prevent deactivating super admin
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Nevar deaktivizēt Super Admin!');
        }

        // Novērš pašdeaktivizāciju
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Nevar deaktivizēt savu kontu!');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'aktivizēts' : 'deaktivizēts';
        return back()->with('success', "Lietotājs veiksmīgi {$status}!");
    }

    /**
     * Atiestata lietotāja paroli
     */
    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Parole veiksmīgi nomainīta!');
    }

    /**
     * Nosūta norādītajam lietotājam e-pastu
     */
    public function sendEmail(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'message' => 'nullable|string|max:5000',
        ]);

        $user = User::findOrFail($validated['user_id']);

        \Mail::raw($validated['message'] ?? '', function ($mail) use ($user, $validated) {
            $mail->to($user->email)
                ->subject($validated['subject'])
                ->from(config('mail.from.address'), config('mail.from.name'));
        });

        return response()->json(['success' => true]);
    }

    /**
     * Noņem norādīto lietotāju
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Neļauj dzēst galveno administratoru
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Nevar dzēst Super Admin!');
        }

        // Novērš pašizdzēšanu
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Nevar dzēst savu kontu!');
        }

        $user->delete();

        return back()->with('success', 'Lietotājs veiksmīgi dzēsts!');
    }
}
