<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingProgram extends Model {
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'title', 'code', 'description', 'objectives',
        'category', 'delivery_method', 'duration_hours', 'cost_per_person',
        'provider', 'certificate_name', 'certificate_validity_months',
        'is_mandatory', 'is_active', 'created_by',
    ];

    protected $casts = [
        'is_mandatory'  => 'boolean',
        'is_active'     => 'boolean',
        'cost_per_person' => 'decimal:2',
    ];

    public function company()  { return $this->belongsTo(Company::class); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
    public function sessions() { return $this->hasMany(TrainingSession::class); }

    public function getCategoryBadgeAttribute(): array {
        return match($this->category) {
            'safety'     => ['bg' => '#FFF5F5', 'color' => '#C53030', 'border' => '#FEB2B2'],
            'technical'  => ['bg' => '#EBF8FF', 'color' => '#2B6CB0', 'border' => '#BEE3F8'],
            'soft_skills'=> ['bg' => '#FAF5FF', 'color' => '#6B46C1', 'border' => '#D6BCFA'],
            'compliance' => ['bg' => '#FFFBEB', 'color' => '#B7791F', 'border' => '#F6E05E'],
            'leadership' => ['bg' => '#FBF5E6', 'color' => '#8B6914', 'border' => '#E8D5A3'],
            'equipment'  => ['bg' => '#F0FBF4', 'color' => '#2D7A4F', 'border' => '#B8E4CA'],
            'onboarding' => ['bg' => '#E6FFFA', 'color' => '#2C7A7B', 'border' => '#81E6D9'],
            default      => ['bg' => '#F7FAFC', 'color' => '#718096', 'border' => '#E2E8F0'],
        };
    }

    public function getDeliveryBadgeAttribute(): array {
        return match($this->delivery_method) {
            'classroom' => ['bg' => '#EBF8FF', 'color' => '#2B6CB0', 'border' => '#BEE3F8'],
            'online'    => ['bg' => '#FAF5FF', 'color' => '#6B46C1', 'border' => '#D6BCFA'],
            'on_job'    => ['bg' => '#F0FBF4', 'color' => '#2D7A4F', 'border' => '#B8E4CA'],
            'workshop'  => ['bg' => '#FBF5E6', 'color' => '#8B6914', 'border' => '#E8D5A3'],
            'blended'   => ['bg' => '#E6FFFA', 'color' => '#2C7A7B', 'border' => '#81E6D9'],
            default     => ['bg' => '#F7FAFC', 'color' => '#718096', 'border' => '#E2E8F0'],
        };
    }
}