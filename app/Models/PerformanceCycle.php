<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceCycle extends Model {
    protected $fillable = [
        'company_id', 'name', 'type', 'start_date', 'end_date',
        'review_start_date', 'review_end_date',
        'status', 'description', 'created_by',
    ];

    protected $casts = [
        'start_date'        => 'date',
        'end_date'          => 'date',
        'review_start_date' => 'date',
        'review_end_date'   => 'date',
    ];

    public function company()    { return $this->belongsTo(Company::class); }
    public function creator()    { return $this->belongsTo(User::class, 'created_by'); }
    public function appraisals() { return $this->hasMany(Appraisal::class); }
    public function goals()      { return $this->hasMany(EmployeeGoal::class); }

    public function getStatusBadgeAttribute(): array {
        return match($this->status) {
            'draft'     => ['bg' => '#F7FAFC', 'color' => '#718096', 'border' => '#E2E8F0'],
            'active'    => ['bg' => '#F0FBF4', 'color' => '#2D7A4F', 'border' => '#B8E4CA'],
            'review'    => ['bg' => '#FFFBEB', 'color' => '#B7791F', 'border' => '#F6E05E'],
            'completed' => ['bg' => '#EBF8FF', 'color' => '#2B6CB0', 'border' => '#BEE3F8'],
            'cancelled' => ['bg' => '#FFF5F5', 'color' => '#C53030', 'border' => '#FEB2B2'],
            default     => ['bg' => '#F7FAFC', 'color' => '#718096', 'border' => '#E2E8F0'],
        };
    }

    public function getDurationAttribute(): string {
        return $this->start_date->format('d M Y')
             . ' – '
             . $this->end_date->format('d M Y');
    }
}