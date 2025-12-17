<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
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
     * Get user's addresses
     */
    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
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
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role && $this->role->name === 'administrator';
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
    }
}
