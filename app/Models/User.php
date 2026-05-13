<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use App\Notifications\VerifyEmailNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Atribūti, kurus var piešķirt masveidā
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'birth_date',
        'country',
        'city',
        'address',
        'postal_code',
        'profile_picture',
        'role_id',
        'is_active',
        'is_public',
        'locale',
        'last_login_at',
    ];

    /**
     * Atribūti, kas serializācijas laikā ir jāslēpj
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Iegūst atribūtus, kas jāraida
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Piekļuves elementi, kas jāpievieno modeļa masīva formai
     */
    protected $appends = ['is_administrator', 'is_super_admin', 'is_courier'];

    /**
     * ATTIECĪBAS
     */

    /**
     * Iegūst lietotāja lomu
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Iegūst administratora ierakstu, ja tāds ir
     */
    public function administrator()
    {
        return $this->hasOne(Administrator::class);
    }

    /**
     * Iegūst lietotāja adreses - atgriež tukšu kolekciju, jo adreses ir lietotāju tabulā
     * Tas ir paredzēts atpakaļsaderībai
     */
    public function addresses()
    {
        // Atgriezt tukšu kolekciju - lietotāja adrese tiek glabāta tieši lietotāju tabulā
        // Piezīme: ja vēlāk izveidošu user_addresses tabulu, tad mainīšu to uz:
        // return $this->hasMany(UserAddress::class);
        return collect([]);
    }

    /**
     * Iegūst lietotāja primāro adresi kā objektu
     */
    public function getPrimaryAddressAttribute()
    {
        if (!$this->address && !$this->city && !$this->country) {
            return null;
        }

        return (object) [
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
        ];
    }

    /**
     * Iegūst lietotāja grozu
     */
    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Iegūst lietotāja groza preces
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Iegūst lietotāja pasūtījumus
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Iegūst lietotāju atsauksmes
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Iegūst lietotāju komentārus
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Iegūst lietotāja izveidoto saturu
     */
    public function content()
    {
        return $this->hasMany(Content::class, 'created_by');
    }

    /**
     * PALĪDZĪBAS METODES
     */

    /**
     * Pārbauda, vai lietotājs ir administrators (ar administratora lomu)
     */
    public function isAdmin(): bool
    {
        return $this->role && $this->role->name === 'administrator';
    }

    /**
     * Pārbauda, vai lietotājs ir administrators (ir administratora ieraksts)
     */
    public function isAdministrator(): bool
    {
        return $this->administrator !== null;
    }

    /**
     * Piekļuves rīks is_administrator atribūtam (lietotāja pusei jeb "frontend")
     */
    public function getIsAdministratorAttribute(): bool
    {
        return $this->isAdministrator();
    }

    /**
     * Pārbauda, vai lietotājs ir galvenais administrators
     */
    public function isSuperAdmin(): bool
    {
        return $this->administrator && $this->administrator->is_super_admin;
    }

    /**
     * Piekļuves rīks is_super_admin atribūtam (lietotāja pusei jeb "frontend")
     */
    public function getIsSuperAdminAttribute(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Piekļuves rīks is_courier atribūtam (lietotāja pusei jeb "frontend")
     */
    public function getIsCourierAttribute(): bool
    {
        return $this->isCourier();
    }

    /**
     * Pārbauda, vai lietotājam ir īpašas administratora atļaujas
     */
    public function hasAdminPermission(string $permission): bool
    {
        if (!$this->administrator) {
            return false;
        }
        return $this->administrator->hasPermission($permission);
    }

    /**
     * Pārbauda, vai lietotājam ir kāda no piešķirtajām administratora atļaujām
     */
    public function hasAnyAdminPermission(array $permissions): bool
    {
        if (!$this->administrator) {
            return false;
        }
        return $this->administrator->hasAnyPermission($permissions);
    }

    public function courier(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Courier::class);
    }

    /**
     * Pārbauda, vai lietotājs ir kurjers
     */
    public function isCourier(): bool
    {
        $this->loadMissing('role');
        return $this->role?->name === 'courier';
    }

    /**
     * Pārbauda, vai lietotājs ir klients
     */
    public function isCustomer(): bool
    {
        return $this->role && $this->role->name === 'customer';
    }

    /**
     * Iegūst lietotāja pilnu vārdu
     */
    public function getFullNameAttribute(): string
    {
        if ($this->first_name && $this->last_name) {
            return "{$this->first_name} {$this->last_name}";
        }
        if ($this->first_name) {
            return $this->first_name;
        }
        return $this->username;
    }

    /**
     * Iegūst lietotāja parādāmo vārdu (lietotāja saskarnei)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->getFullNameAttribute();
    }

    /**
     * Atjaunina pēdējās pieteikšanās laika zīmogu
     */
    public function updateLastLogin(): void
    {
        // Atjaunina tikai tad, ja kolonna pastāv
        if (Schema::hasColumn('users', 'last_login_at')) {
            $this->update(['last_login_at' => now()]);
        }

        // Atjaunina arī administratora pēdējo pieteikšanās reizi, ja piemērojams
        if ($this->administrator) {
            $this->administrator->updateLastLogin();
        }
    }

    /**
     * Izmanto custom VerifyEmailNotification ar verify-email.blade.php veidni,
     * nevis Laravel noklusējuma teksta e-pastu.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }
}
