<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Location extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id','code','name','type','address','city','province',
        'latitude','longitude','radius_meters','qr_token','qr_rotated_at',
        'is_active','notes',
    ];

    protected $casts = [
        'latitude'       => 'decimal:7',
        'longitude'      => 'decimal:7',
        'radius_meters'  => 'integer',
        'is_active'      => 'boolean',
        'qr_rotated_at'  => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────
    public function company()  { return $this->belongsTo(Company::class); }
    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_locations')
            ->withPivot('is_primary','assigned_from','assigned_until')
            ->withTimestamps();
    }  
     public function attendances(){ return $this->hasMany(Attendance::class); }

    // ── Helpers ───────────────────────────────────────────
    /**
     * Distance in meters between this location and given coords (Haversine).
     */
    public function distanceTo(float $lat, float $lng): float
    {
        $earthRadius = 6371000; // meters
        $lat1 = deg2rad((float) $this->latitude);
        $lat2 = deg2rad($lat);
        $dLat = deg2rad($lat - (float) $this->latitude);
        $dLng = deg2rad($lng - (float) $this->longitude);

        $a = sin($dLat/2) ** 2 + cos($lat1) * cos($lat2) * sin($dLng/2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function isWithinRadius(float $lat, float $lng): bool
    {
        return $this->distanceTo($lat, $lng) <= $this->radius_meters;
    }

    /**
     * Generate a fresh QR token. Call on create + admin rotation.
     */
    public static function generateQrToken(): string
    {
        return Str::random(48);
    }

    public function typeLabel(): string
    {
        return match($this->type) {
            'warehouse' => 'Warehouse',
            'office'    => 'Office',
            'site'      => 'Site',
            'branch'    => 'Branch',
            default     => 'Other',
        };
    }

    public function typeIcon(): string
    {
        return match($this->type) {
            'warehouse' => 'fa-warehouse',
            'office'    => 'fa-building',
            'site'      => 'fa-person-digging',
            'branch'    => 'fa-store',
            default     => 'fa-location-dot',
        };
    }
}