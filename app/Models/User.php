<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasRoles, Notifiable;


    protected $fillable = [
        'company_id', 'name', 'email', 'username',
        'password', 'avatar', 'phone',
        'user_type', 'portal_access',
        'is_active',
        'two_factor_enabled', 'two_factor_secret',
        'two_factor_recovery_codes', 'two_factor_confirmed_at',
        'last_login_at', 'last_login_ip',
        'failed_login_attempts', 'locked_until',
        'login_count',
        'password_changed_at', 'last_password_changed_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
        'two_factor_secret', 'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at'         => 'datetime',
        'last_login_at'             => 'datetime',
        'locked_until'              => 'datetime',
        'two_factor_confirmed_at'   => 'datetime',
        'password_changed_at'       => 'datetime',
        'last_password_changed_at'  => 'datetime',
        'is_active'                 => 'boolean',
        'two_factor_enabled'        => 'boolean',
        'two_factor_recovery_codes' => 'array',
        'password'                  => 'hashed',
    ];

    // ── Relationships ─────────────────────────────────────
    public function company()  { return $this->belongsTo(Company::class); }
    public function employee() { return $this->hasOne(Employee::class); }

    // ── Helpers ───────────────────────────────────────────
    public function isAdmin(): bool {
        return in_array($this->user_type, ['admin', 'super_admin']);
    }

    public function isEmployee(): bool {
        return $this->user_type === 'employee';
    }

    public function isSuperAdmin(): bool {
        return $this->user_type === 'super_admin';
    }

    public function canAccessAdminPortal(): bool {
        return in_array($this->portal_access, ['admin', 'both'])
            && $this->isAdmin();
    }

    public function canAccessEmployeePortal(): bool {
        return in_array($this->portal_access, ['employee', 'both']);
    }

    public function isLocked(): bool {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function getAvatarUrlAttribute(): string {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name)
              . '&background=C49A3C&color=1C2331&bold=true&size=128';
    }

    public function getDisplayRoleAttribute(): string {
        return $this->roles->first()?->name
            ? ucwords(str_replace('_', ' ', $this->roles->first()->name))
            : 'No Role';
    }
}