<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Administrator extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'administrators';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'full_name',
        'permissions',
        'is_super_admin',
        'last_login_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'permissions' => 'array',
        'is_super_admin' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Available permissions in the system.
     * Super admin has ALL permissions automatically.
     */
    public const PERMISSIONS = [
        // Produktu pārvaldība
        'products.view' => 'Skatīt produktus',
        'products.create' => 'Izveidot produktus',
        'products.edit' => 'Rediģēt produktus',
        'products.delete' => 'Dzēst produktus',

        // Kategoriju pārvaldība
        'categories.view' => 'Skatīt kategorijas',
        'categories.create' => 'Izveidot kategorijas',
        'categories.edit' => 'Rediģēt kategorijas',
        'categories.delete' => 'Dzēst kategorijas',

        // Pasūtījumu pārvaldība
        'orders.view' => 'Skatīt pasūtījumus',
        'orders.edit' => 'Rediģēt pasūtījumus',
        'orders.delete' => 'Dzēst pasūtījumus',

        // Lietotāju pārvaldība
        'users.view' => 'Skatīt lietotājus',
        'users.create' => 'Izveidot lietotājus',
        'users.edit' => 'Rediģēt lietotājus',
        'users.delete' => 'Dzēst lietotājus',
        'users.ban' => 'Bloķēt lietotājus',

        // Satura pārvaldība
        'content.view' => 'Skatīt saturu',
        'content.create' => 'Izveidot saturu',
        'content.edit' => 'Rediģēt saturu',
        'content.delete' => 'Dzēst saturu',
        'content.publish' => 'Publicēt saturu',

        // Atsauksmju un komentāru moderēšana
        'reviews.view' => 'Skatīt atsauksmes',
        'reviews.moderate' => 'Moderēt atsauksmes',
        'reviews.delete' => 'Dzēst atsauksmes',
        'comments.view' => 'Skatīt komentārus',
        'comments.moderate' => 'Moderēt komentārus',
        'comments.delete' => 'Dzēst komentārus',

        // Kontaktu ziņojumi
        'contacts.view' => 'Skatīt kontakta ziņojumus',
        'contacts.reply' => 'Atbildēt uz ziņojumiem',
        'contacts.delete' => 'Dzēst ziņojumus',

        // Sistēmas iestatījumi
        'settings.view' => 'Skatīt iestatījumus',
        'settings.edit' => 'Rediģēt iestatījumus',

        // Administratoru pārvaldība (tikai super admin)
        'admins.view' => 'Skatīt administratorus',
        'admins.create' => 'Izveidot administratorus',
        'admins.edit' => 'Rediģēt administratorus',
        'admins.delete' => 'Dzēst administratorus',

        // Aktivitāšu žurnāls
        'logs.view' => 'Skatīt aktivitāšu žurnālu',
    ];

    /**
     * Permission groups for UI organization.
     */
    public const PERMISSION_GROUPS = [
        'Produkti' => ['products.view', 'products.create', 'products.edit', 'products.delete'],
        'Kategorijas' => ['categories.view', 'categories.create', 'categories.edit', 'categories.delete'],
        'Pasūtījumi' => ['orders.view', 'orders.edit', 'orders.delete'],
        'Lietotāji' => ['users.view', 'users.create', 'users.edit', 'users.delete', 'users.ban'],
        'Saturs' => ['content.view', 'content.create', 'content.edit', 'content.delete', 'content.publish'],
        'Atsauksmes' => ['reviews.view', 'reviews.moderate', 'reviews.delete'],
        'Komentāri' => ['comments.view', 'comments.moderate', 'comments.delete'],
        'Kontakti' => ['contacts.view', 'contacts.reply', 'contacts.delete'],
        'Iestatījumi' => ['settings.view', 'settings.edit'],
        'Administratori' => ['admins.view', 'admins.create', 'admins.edit', 'admins.delete'],
        'Žurnāls' => ['logs.view'],
    ];

    /**
     * Super admin email (only one allowed).
     */
    public const SUPER_ADMIN_EMAIL = 'ralphmania.roltonslv@gmail.com';

    /**
     * Get the user that owns this administrator record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if administrator has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        // Super admin has all permissions
        if ($this->is_super_admin) {
            return true;
        }

        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Check if administrator has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if administrator has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Grant permissions to administrator.
     */
    public function grantPermissions(array $permissions): void
    {
        $current = $this->permissions ?? [];
        $this->permissions = array_unique(array_merge($current, $permissions));
        $this->save();
    }

    /**
     * Revoke permissions from administrator.
     */
    public function revokePermissions(array $permissions): void
    {
        $current = $this->permissions ?? [];
        $this->permissions = array_values(array_diff($current, $permissions));
        $this->save();
    }

    /**
     * Set all permissions at once.
     */
    public function setPermissions(array $permissions): void
    {
        $this->permissions = $permissions;
        $this->save();
    }

    /**
     * Get all available permissions.
     */
    public static function getAvailablePermissions(): array
    {
        return self::PERMISSIONS;
    }

    /**
     * Get permissions grouped for UI.
     */
    public static function getPermissionGroups(): array
    {
        return self::PERMISSION_GROUPS;
    }

    /**
     * Update last login timestamp.
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Check if this is the super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin;
    }

    /**
     * Scope for super admins only.
     */
    public function scopeSuperAdmins($query)
    {
        return $query->where('is_super_admin', true);
    }

    /**
     * Scope for regular admins (not super).
     */
    public function scopeRegularAdmins($query)
    {
        return $query->where('is_super_admin', false);
    }
}
