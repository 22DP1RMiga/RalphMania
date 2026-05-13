<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AdminAdministratorController extends Controller
{
    /**
     * Pārbauda, ​​vai pašreizējais lietotājs ir galvenais administrators jeb "super admins"
     */
    private function checkSuperAdmin()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Tikai Super Admin var piekļūt šai sadaļai.');
        }
    }

    /**
     * Parāda administratoru sarakstu
     */
    public function index(Request $request)
    {
        $this->checkSuperAdmin();

        $query = Administrator::with('user');

        // Meklētājam
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function($userQuery) use ($search) {
                        $userQuery->where('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filtrē pēc tipa
        if ($request->filled('type')) {
            if ($request->type === 'super') {
                $query->where('is_super_admin', true);
            } else {
                $query->where('is_super_admin', false);
            }
        }

        // Kārtošanai
        $query->orderBy('is_super_admin', 'desc')
            ->orderBy('created_at', 'desc');

        // Iegūst administratorus
        $administrators = $query->paginate(15)->through(function ($admin) {
            return [
                'id' => $admin->id,
                'user_id' => $admin->user_id,
                'full_name' => $admin->full_name,
                'permissions' => $admin->permissions ?? [],
                'is_super_admin' => $admin->is_super_admin,
                'last_login_at' => $admin->last_login_at,
                'created_at' => $admin->created_at,
                'user' => $admin->user ? [
                    'id' => $admin->user->id,
                    'username' => $admin->user->username,
                    'email' => $admin->user->email,
                    'profile_picture' => $admin->user->profile_picture,
                    'is_active' => $admin->user->is_active,
                ] : null,
            ];
        });

        // Iegūst statusus
        $stats = [
            'total' => Administrator::count(),
            'super_admins' => Administrator::where('is_super_admin', true)->count(),
            'regular_admins' => Administrator::where('is_super_admin', false)->count(),
        ];

        // Iegūst pieejamās atļaujas modālajam logam
        $availablePermissions = Administrator::getAvailablePermissions();
        $permissionGroups = Administrator::getPermissionGroups();

        // Iegūst lietotājus, kuri vēl nav administratori (jaunu administratoru pievienošanai)
        $availableUsers = User::whereDoesntHave('administrator')
            ->where('is_active', true)
            ->orderBy('username')
            ->get(['id', 'username', 'email', 'profile_picture']);

        return Inertia::render('Admin/Administrators/Index', [
            'administrators' => $administrators,
            'filters' => $request->only(['search', 'type']),
            'stats' => $stats,
            'availablePermissions' => $availablePermissions,
            'permissionGroups' => $permissionGroups,
            'availableUsers' => $availableUsers,
        ]);
    }

    /**
     * Saglabā jaunu administratoru
     */
    public function store(Request $request)
    {
        $this->checkSuperAdmin();

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:administrators,user_id',
            'full_name' => 'required|string|max:100',
            'permissions' => 'array',
            'permissions.*' => 'string',
        ]);

        // Iegūst administratora lomu
        $adminRole = Role::where('name', 'administrator')->first();

        if (!$adminRole) {
            return back()->with('error', 'Administratora loma nav atrasta!');
        }

        // Atjaunina lietotāja lomu
        $user = User::findOrFail($validated['user_id']);
        $user->update(['role_id' => $adminRole->id]);

        // Izveido administratora ierakstu
        Administrator::create([
            'user_id' => $validated['user_id'],
            'full_name' => $validated['full_name'],
            'permissions' => $validated['permissions'] ?? [],
            'is_super_admin' => false,
        ]);

        return back()->with('success', 'Administrators veiksmīgi pievienots!');
    }

    /**
     * Atjaunina administratora atļaujas
     */
    public function updatePermissions(Request $request, $id)
    {
        $this->checkSuperAdmin();

        $admin = Administrator::findOrFail($id);

        // Nevar mainīt "super admina" atļaujas
        if ($admin->is_super_admin) {
            return back()->with('error', 'Nevar modificēt Super Admin atļaujas!');
        }

        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'string',
        ]);

        $admin->update([
            'permissions' => $validated['permissions'] ?? [],
        ]);

        return back()->with('success', 'Atļaujas veiksmīgi atjauninātas!');
    }

    /**
     * Noņem administratoru
     */
    public function destroy($id)
    {
        $this->checkSuperAdmin();

        $admin = Administrator::findOrFail($id);

        // Nevar izdzēst galveno administratoru
        if ($admin->is_super_admin) {
            return back()->with('error', 'Nevar dzēst Super Admin!');
        }

        // Nevar izdzēst sevi
        if ($admin->user_id === auth()->id()) {
            return back()->with('error', 'Nevar dzēst savu administratora kontu!');
        }

        // Iegūst lietotāja lomu (pazemina no administratora atpakaļ uz parasto lietotāju)
        $userRole = Role::whereIn('name', ['user', 'customer'])->first();

        if ($userRole && $admin->user) {
            // Pazemina līdz parastajam lietotājam
            $admin->user->update(['role_id' => $userRole->id]);
        }

        // Dzēš administratora ierakstu
        $admin->delete();

        return back()->with('success', 'Administrators veiksmīgi noņemts!');
    }
}
