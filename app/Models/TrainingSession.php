<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingSession extends Model {
    protected $fillable = [
        'training_program_id', 'company_id', 'session_code', 'title',
        'start_date', 'end_date', 'start_time', 'end_time',
        'venue', 'trainer_name', 'trainer_email',
        'max_participants', 'enrolled_count', 'status',
        'actual_cost', 'notes', 'feedback_summary',
        'average_rating', 'created_by',
    ];

    protected $casts = [
        'start_date'    => 'date',
        'end_date'      => 'date',
        'actual_cost'   => 'decimal:2',
        'average_rating'=> 'decimal:1',
    ];

    public function program()     { return $this->belongsTo(TrainingProgram::class, 'training_program_id'); }
    public function company()     { return $this->belongsTo(Company::class); }
    public function creator()     { return $this->belongsTo(User::class, 'created_by'); }
    public function enrollments() { return $this->hasMany(TrainingEnrollment::class); }

    public function getStatusBadgeAttribute(): array {
        return match($this->status) {
            'scheduled'  => ['bg' => '#EBF8FF', 'color' => '#2B6CB0', 'border' => '#BEE3F8'],
            'ongoing'    => ['bg' => '#F0FBF4', 'color' => '#2D7A4F', 'border' => '#B8E4CA'],
            'completed'  => ['bg' => '#FBF5E6', 'color' => '#8B6914', 'border' => '#E8D5A3'],
            'cancelled'  => ['bg' => '#FFF5F5', 'color' => '#C53030', 'border' => '#FEB2B2'],
            'postponed'  => ['bg' => '#FFFBEB', 'color' => '#B7791F', 'border' => '#F6E05E'],
            default      => ['bg' => '#F7FAFC', 'color' => '#718096', 'border' => '#E2E8F0'],
        };
    }

    public function getSeatAvailableAttribute(): int {
        return max(0, $this->max_participants - $this->enrolled_count);
    }

    public function isFull(): bool {
        return $this->enrolled_count >= $this->max_participants;
    }
}