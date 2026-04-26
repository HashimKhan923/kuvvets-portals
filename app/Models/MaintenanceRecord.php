<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceRecord extends Model {
    protected $fillable = [
        'asset_id', 'company_id', 'reference_number', 'type', 'status',
        'scheduled_date', 'completed_date', 'performed_by', 'vendor',
        'cost', 'downtime_hours', 'description', 'work_done',
        'parts_replaced', 'next_service_date', 'odometer_reading',
        'operating_hours', 'created_by',
    ];

    protected $casts = [
        'scheduled_date'  => 'date',
        'completed_date'  => 'date',
        'next_service_date'=> 'date',
        'cost'            => 'decimal:2',
    ];

    public function asset()   { return $this->belongsTo(Asset::class); }
    public function company() { return $this->belongsTo(Company::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public function getStatusBadgeAttribute(): array {
        return match($this->status) {
            'scheduled'   => ['bg'=>'#EBF8FF','color'=>'#2B6CB0','border'=>'#BEE3F8'],
            'in_progress' => ['bg'=>'#FFFBEB','color'=>'#B7791F','border'=>'#F6E05E'],
            'completed'   => ['bg'=>'#F0FBF4','color'=>'#2D7A4F','border'=>'#B8E4CA'],
            'cancelled'   => ['bg'=>'#FFF5F5','color'=>'#C53030','border'=>'#FEB2B2'],
            default       => ['bg'=>'#F7FAFC','color'=>'#718096','border'=>'#E2E8F0'],
        };
    }

    public function getTypeBadgeAttribute(): array {
        return match($this->type) {
            'routine'     => ['bg'=>'#F0FBF4','color'=>'#2D7A4F','border'=>'#B8E4CA'],
            'preventive'  => ['bg'=>'#EBF8FF','color'=>'#2B6CB0','border'=>'#BEE3F8'],
            'corrective'  => ['bg'=>'#FFFBEB','color'=>'#B7791F','border'=>'#F6E05E'],
            'emergency'   => ['bg'=>'#FFF5F5','color'=>'#C53030','border'=>'#FEB2B2'],
            'inspection'  => ['bg'=>'#FBF5E6','color'=>'#8B6914','border'=>'#E8D5A3'],
            'calibration' => ['bg'=>'#FAF5FF','color'=>'#6B46C1','border'=>'#D6BCFA'],
            default       => ['bg'=>'#F7FAFC','color'=>'#718096','border'=>'#E2E8F0'],
        };
    }
}