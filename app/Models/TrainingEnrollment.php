<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingEnrollment extends Model {
    protected $fillable = [
        'training_session_id', 'employee_id', 'enrolled_by',
        'status', 'completed', 'score', 'passed',
        'feedback_rating', 'feedback_comments',
        'completion_date', 'certificate_number', 'certificate_expiry',
    ];

    protected $casts = [
        'completed'         => 'boolean',
        'passed'            => 'boolean',
        'completion_date'   => 'date',
        'certificate_expiry'=> 'date',
    ];

    public function session()  { return $this->belongsTo(TrainingSession::class, 'training_session_id'); }
    public function employee() { return $this->belongsTo(Employee::class); }
    public function enrolledBy(){ return $this->belongsTo(User::class, 'enrolled_by'); }

    public function getStatusBadgeAttribute(): array {
        return match($this->status) {
            'enrolled'   => ['bg' => '#EBF8FF', 'color' => '#2B6CB0', 'border' => '#BEE3F8'],
            'attended'   => ['bg' => '#F0FBF4', 'color' => '#2D7A4F', 'border' => '#B8E4CA'],
            'absent'     => ['bg' => '#FFF5F5', 'color' => '#C53030', 'border' => '#FEB2B2'],
            'cancelled'  => ['bg' => '#F7FAFC', 'color' => '#718096', 'border' => '#E2E8F0'],
            'waitlisted' => ['bg' => '#FFFBEB', 'color' => '#B7791F', 'border' => '#F6E05E'],
            default      => ['bg' => '#F7FAFC', 'color' => '#718096', 'border' => '#E2E8F0'],
        };
    }
}