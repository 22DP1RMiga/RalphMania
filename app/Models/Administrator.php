<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Administrator extends Model
{
    use HasFactory;

    /**
     * Ar modeli saistītā tabula
     */
    protected $table = 'administrators';

    /**
     * Atribūti, kurus var piešķirt masveidā
     */
    protected $fillable = [
        'user_id',
        'full_name',
        'permissions',
        'is_super_admin',
        'last_login_at',
    ];

    /**
     * Atribūti, kas jāpielieto
     */
    protected $casts = [
        'permissions' => 'array',
        'is_super_admin' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Sistēmā pieejamās atļaujas
     * Galvenajam administratoram automātiski ir visas atļaujas
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
        'orders.export' => 'Eksportēt pasūtījumus',

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

        // Kurjeru pārvaldība
        'couriers.view' => 'Skatīt kurjerus',
        'couriers.create' => 'Pievienot kurjerus',
        'couriers.edit' => 'Rediģēt kurjerus',
        'couriers.delete' => 'Dzēst kurjerus',
        'couriers.assign' => 'Piešķirt pasūtījumus kurjeriem',

        // Sistēmas iestatījumi
        'settings.view' => 'Skatīt iestatījumus',
        'settings.edit' => 'Rediģēt iestatījumus',

        // Administratoru pārvaldība (tikai galvenais adminstrators)
        'admins.view' => 'Skatīt administratorus',
        'admins.create' => 'Izveidot administratorus',
        'admins.edit' => 'Rediģēt administratorus',
        'admins.delete' => 'Dzēst administratorus',

        // Aktivitāšu žurnāls
        'logs.view' => 'Skatīt aktivitāšu žurnālu',
        'logs.export' => 'Eksportēt aktivitāšu žurnālu',
    ];

    /**
     * Atļauju grupas lietotāja interfeisa organizēšanai
     */
    public const PERMISSION_GROUPS = [
        'Produkti'       => ['products.view', 'products.create', 'products.edit', 'products.delete'],
        'Kategorijas'    => ['categories.view', 'categories.create', 'categories.edit', 'categories.delete'],
        'Pasūtījumi'     => ['orders.view', 'orders.edit', 'orders.delete', 'orders.export'],
        'Lietotāji'      => ['users.view', 'users.create', 'users.edit', 'users.delete', 'users.ban'],
        'Saturs'         => ['content.view', 'content.create', 'content.edit', 'content.delete', 'content.publish'],
        'Atsauksmes'     => ['reviews.view', 'reviews.moderate', 'reviews.delete'],
        'Komentāri'      => ['comments.view', 'comments.moderate', 'comments.delete'],
        'Kontakti'       => ['contacts.view', 'contacts.reply', 'contacts.delete'],
        'Kurjeri'        => ['couriers.view', 'couriers.create', 'couriers.edit', 'couriers.delete', 'couriers.assign'],
        'Iestatījumi'    => ['settings.view', 'settings.edit'],
        'Administratori' => ['admins.view', 'admins.create', 'admins.edit', 'admins.delete'],
        'Žurnāls'        => ['logs.view', 'logs.export'],
    ];

    /**
     * Galvenā adminstratora e-pasts (tikai vienīgais atļauts).
     */
    public const SUPER_ADMIN_EMAIL = 'ralphmania.roltonslv@gmail.com';

    /**
     * Iegūst lietotāju, kuram pieder šis administratora ieraksts
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Pārbauda, vai administratoram ir īpašas atļaujas
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
     * Pārbauda, vai administratoram ir kāda no piešķirtajām atļaujām
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
     * Pārbauda, vai administratoram ir visas nepieciešamās atļaujas
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
     * Piešķir atļaujas administratoram
     */
    public function grantPermissions(array $permissions): void
    {
        $current = $this->permissions ?? [];
        $this->permissions = array_unique(array_merge($current, $permissions));
        $this->save();
    }

    /**
     * Atsauc administratora atļaujas
     */
    public function revokePermissions(array $permissions): void
    {
        $current = $this->permissions ?? [];
        $this->permissions = array_values(array_diff($current, $permissions));
        $this->save();
    }

    /**
     * Iestata visas atļaujas vienlaikus
     */
    public function setPermissions(array $permissions): void
    {
        $this->permissions = $permissions;
        $this->save();
    }

    /**
     * Iegūst visas pieejamās atļaujas
     */
    public static function getAvailablePermissions(): array
    {
        return self::PERMISSIONS;
    }

    /**
     * Iegūst lietotāja interfeisam grupētas atļaujas
     */
    public static function getPermissionGroups(): array
    {
        return self::PERMISSION_GROUPS;
    }

    /**
     * Atjaunina pēdējās pieteikšanās laika zīmogu
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Pārbauda, vai šis ir galvenais administrators
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin;
    }

    /**
     * Tvērums tikai galveniem adminstratoriem (pagaidām tikai vienam)
     */
    public function scopeSuperAdmins($query)
    {
        return $query->where('is_super_admin', true);
    }

    /**
     * Tvērums parastiem administratoriem (nevis galveniem).
     */
    public function scopeRegularAdmins($query)
    {
        return $query->where('is_super_admin', false);
    }
}
