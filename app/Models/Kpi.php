<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kpi extends Model {
    protected $fillable = [
        'company_id', 'department_id', 'title', 'description',
        'category', 'measurement_type', 'unit',
        'target_value', 'min_value', 'max_value',
        'weight', 'is_active',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'target_value' => 'decimal:2',
        'min_value'    => 'decimal:2',
        'max_value'    => 'decimal:2',
    ];

    public function company()    { return $this->belongsTo(Company::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function goals()      { return $this->hasMany(EmployeeGoal::class); }

    public function getCategoryBadgeAttribute(): array {
        return match($this->category) {
            'productivity' => ['bg' => '#EBF8FF', 'color' => '#2B6CB0', 'border' => '#BEE3F8'],
            'quality'      => ['bg' => '#F0FBF4', 'color' => '#2D7A4F', 'border' => '#B8E4CA'],
            'attendance'   => ['bg' => '#FFFBEB', 'color' => '#B7791F', 'border' => '#F6E05E'],
            'customer'     => ['bg' => '#FBF5E6', 'color' => '#8B6914', 'border' => '#E8D5A3'],
            'financial'    => ['bg' => '#F0FFF4', 'color' => '#276749', 'border' => '#9AE6B4'],
            'learning'     => ['bg' => '#FAF5FF', 'color' => '#6B46C1', 'border' => '#D6BCFA'],
            'leadership'   => ['bg' => '#FFF5F5', 'color' => '#C53030', 'border' => '#FEB2B2'],
            default        => ['bg' => '#F7FAFC', 'color' => '#718096', 'border' => '#E2E8F0'],
        };
    }
}