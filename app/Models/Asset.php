<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model {
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'asset_category_id', 'department_id',
        'asset_code', 'name', 'brand', 'model', 'serial_number',
        'registration_number', 'type', 'condition', 'status', 'ownership',
        'purchase_date', 'purchase_cost', 'current_value', 'depreciation_rate',
        'vendor', 'vendor_contact', 'warranty_expiry', 'insurance_expiry',
        'insurance_policy', 'license_expiry', 'odometer_reading',
        'operating_hours', 'notes', 'image', 'location', 'created_by',
    ];

    protected $casts = [
        'purchase_date'    => 'date',
        'warranty_expiry'  => 'date',
        'insurance_expiry' => 'date',
        'license_expiry'   => 'date',
        'purchase_cost'    => 'decimal:2',
        'current_value'    => 'decimal:2',
        'depreciation_rate'=> 'decimal:2',
    ];

    public function company()      { return $this->belongsTo(Company::class); }
    public function category()     { return $this->belongsTo(AssetCategory::class, 'asset_category_id'); }
    public function department()   { return $this->belongsTo(Department::class); }
    public function creator()      { return $this->belongsTo(User::class, 'created_by'); }
    public function assignments()  { return $this->hasMany(AssetAssignment::class); }
    public function maintenance()  { return $this->hasMany(MaintenanceRecord::class); }
    public function rentals()      { return $this->hasMany(RentalContract::class); }

    public function currentAssignment() {
        return $this->hasOne(AssetAssignment::class)->where('status', 'active')->latest();
    }

    public function getStatusBadgeAttribute(): array {
        return match($this->status) {
            'available'          => ['bg'=>'#F0FBF4','color'=>'#2D7A4F','border'=>'#B8E4CA'],
            'assigned'           => ['bg'=>'#EBF8FF','color'=>'#2B6CB0','border'=>'#BEE3F8'],
            'under_maintenance'  => ['bg'=>'#FFFBEB','color'=>'#B7791F','border'=>'#F6E05E'],
            'out_of_service'     => ['bg'=>'#FFF5F5','color'=>'#C53030','border'=>'#FEB2B2'],
            'disposed'           => ['bg'=>'#F7FAFC','color'=>'#718096','border'=>'#E2E8F0'],
            'rented_out'         => ['bg'=>'#FAF5FF','color'=>'#6B46C1','border'=>'#D6BCFA'],
            default              => ['bg'=>'#F7FAFC','color'=>'#718096','border'=>'#E2E8F0'],
        };
    }

    public function getConditionBadgeAttribute(): array {
        return match($this->condition) {
            'new'          => ['bg'=>'#F0FBF4','color'=>'#2D7A4F','border'=>'#B8E4CA'],
            'good'         => ['bg'=>'#EBF8FF','color'=>'#2B6CB0','border'=>'#BEE3F8'],
            'fair'         => ['bg'=>'#FFFBEB','color'=>'#B7791F','border'=>'#F6E05E'],
            'poor'         => ['bg'=>'#FFF5F5','color'=>'#C53030','border'=>'#FEB2B2'],
            'under_repair' => ['bg'=>'#FFFBEB','color'=>'#B7791F','border'=>'#F6E05E'],
            'disposed'     => ['bg'=>'#F7FAFC','color'=>'#718096','border'=>'#E2E8F0'],
            default        => ['bg'=>'#F7FAFC','color'=>'#718096','border'=>'#E2E8F0'],
        };
    }

    public function getTypeIconAttribute(): string {
        return match($this->type) {
            'heavy_equipment'    => 'fa-industry',
            'vehicle'            => 'fa-truck',
            'forklift'           => 'fa-forklift',
            'crane'              => 'fa-person-digging',
            'warehouse_equipment'=> 'fa-warehouse',
            'it_equipment'       => 'fa-computer',
            'furniture'          => 'fa-chair',
            'tools'              => 'fa-wrench',
            'safety_equipment'   => 'fa-helmet-safety',
            default              => 'fa-box',
        };
    }

    public function isWarrantyExpired(): bool {
        return $this->warranty_expiry && $this->warranty_expiry->isPast();
    }

    public function isInsuranceExpiring(): bool {
        return $this->insurance_expiry
            && $this->insurance_expiry->isFuture()
            && $this->insurance_expiry->diffInDays(now()) <= 30;
    }

    public function isInsuranceExpired(): bool {
        return $this->insurance_expiry && $this->insurance_expiry->isPast();
    }

    public function getDepreciatedValueAttribute(): float {
        if (!$this->purchase_cost || !$this->purchase_date) return 0;
        $years = $this->purchase_date->diffInYears(now());
        $rate  = $this->depreciation_rate / 100;
        return max(0, round($this->purchase_cost * pow(1 - $rate, $years), 2));
    }
}