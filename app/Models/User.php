<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
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
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
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
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = ['is_administrator', 'is_super_admin'];

    /**
     * RELATIONSHIPS
     */

    /**
     * Get the role of the user
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get administrator record if exists
     */
    public function administrator()
    {
        return $this->hasOne(Administrator::class);
    }

    /**
     * Get user's addresses - returns empty collection since addresses are in users table
     * This is for backwards compatibility
     */
    public function addresses()
    {
        // Return empty collection - user address is stored directly in users table
        // If you create user_addresses table later, change this to:
        // return $this->hasMany(UserAddress::class);
        return collect([]);
    }

    /**
     * Get user's primary address as object
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
     * Get user's cart
     */
    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Get user's cart items
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get user's orders
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get user's reviews
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get user's comments
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get user's created content
     */
    public function content()
    {
        return $this->hasMany(Content::class, 'created_by');
    }

    /**
     * HELPER METHODS
     */

    /**
     * Check if user is admin (has administrator role)
     */
    public function isAdmin(): bool
    {
        return $this->role && $this->role->name === 'administrator';
    }

    /**
     * Check if user is an administrator (has administrator record)
     */
    public function isAdministrator(): bool
    {
        return $this->administrator !== null;
    }

    /**
     * Accessor for is_administrator attribute (for frontend)
     */
    public function getIsAdministratorAttribute(): bool
    {
        return $this->isAdministrator();
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->administrator && $this->administrator->is_super_admin;
    }

    /**
     * Accessor for is_super_admin attribute (for frontend)
     */
    public function getIsSuperAdminAttribute(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Check if user has a specific admin permission
     */
    public function hasAdminPermission(string $permission): bool
    {
        if (!$this->administrator) {
            return false;
        }
        return $this->administrator->hasPermission($permission);
    }

    /**
     * Check if user has any of the given admin permissions
     */
    public function hasAnyAdminPermission(array $permissions): bool
    {
        if (!$this->administrator) {
            return false;
        }
        return $this->administrator->hasAnyPermission($permissions);
    }

    /**
     * Check if user is courier
     */
    public function isCourier(): bool
    {
        return $this->role && $this->role->name === 'courier';
    }

    /**
     * Check if user is customer
     */
    public function isCustomer(): bool
    {
        return $this->role && $this->role->name === 'customer';
    }

    /**
     * Get user's full name
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
     * Get user's display name (for UI)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->getFullNameAttribute();
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        // Only update if column exists
        if (Schema::hasColumn('users', 'last_login_at')) {
            $this->update(['last_login_at' => now()]);
        }

        // Also update administrator last login if applicable
        if ($this->administrator) {
            $this->administrator->updateLastLogin();
        }
    }
}
