<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appraisal extends Model {
    protected $fillable = [
        'performance_cycle_id', 'employee_id', 'appraiser_id', 'company_id',
        'appraisal_number', 'type', 'status',
        'job_knowledge_score', 'work_quality_score', 'productivity_score',
        'communication_score', 'teamwork_score', 'initiative_score',
        'attendance_score', 'leadership_score', 'goal_achievement_score',
        'overall_score',
        'strengths', 'improvements', 'achievements',
        'training_needs', 'manager_comments', 'hr_comments', 'employee_response',
        'overall_rating', 'increment_recommended', 'promotion_recommended',
        'promotion_notes', 'submitted_at', 'completed_at',
    ];

    protected $casts = [
        'submitted_at'          => 'datetime',
        'completed_at'          => 'datetime',
        'promotion_recommended' => 'boolean',
        'overall_score'         => 'decimal:2',
        'increment_recommended' => 'decimal:2',
    ];

    public function cycle()    { return $this->belongsTo(PerformanceCycle::class, 'performance_cycle_id'); }
    public function employee() { return $this->belongsTo(Employee::class); }
    public function appraiser(){ return $this->belongsTo(User::class, 'appraiser_id'); }
    public function company()  { return $this->belongsTo(Company::class); }
    public function feedback() { return $this->hasMany(Feedback360::class); }

    public function getStatusBadgeAttribute(): array {
        return match($this->status) {
            'pending'        => ['bg' => '#F7FAFC', 'color' => '#718096', 'border' => '#E2E8F0'],
            'self_review'    => ['bg' => '#FFFBEB', 'color' => '#B7791F', 'border' => '#F6E05E'],
            'manager_review' => ['bg' => '#EBF8FF', 'color' => '#2B6CB0', 'border' => '#BEE3F8'],
            'hr_review'      => ['bg' => '#FBF5E6', 'color' => '#8B6914', 'border' => '#E8D5A3'],
            'completed'      => ['bg' => '#F0FBF4', 'color' => '#2D7A4F', 'border' => '#B8E4CA'],
            default          => ['bg' => '#F7FAFC', 'color' => '#718096', 'border' => '#E2E8F0'],
        };
    }

    public function getRatingBadgeAttribute(): array {
        return match($this->overall_rating) {
            'outstanding'           => ['bg' => '#F0FFF4', 'color' => '#276749', 'border' => '#9AE6B4',  'label' => 'Outstanding'],
            'exceeds_expectations'  => ['bg' => '#F0FBF4', 'color' => '#2D7A4F', 'border' => '#B8E4CA',  'label' => 'Exceeds Expectations'],
            'meets_expectations'    => ['bg' => '#EBF8FF', 'color' => '#2B6CB0', 'border' => '#BEE3F8',  'label' => 'Meets Expectations'],
            'needs_improvement'     => ['bg' => '#FFFBEB', 'color' => '#B7791F', 'border' => '#F6E05E',  'label' => 'Needs Improvement'],
            'unsatisfactory'        => ['bg' => '#FFF5F5', 'color' => '#C53030', 'border' => '#FEB2B2',  'label' => 'Unsatisfactory'],
            default                 => ['bg' => '#F7FAFC', 'color' => '#718096', 'border' => '#E2E8F0',  'label' => 'Not Rated'],
        };
    }

    public function calculateOverallScore(): float {
        $scores = array_filter([
            $this->job_knowledge_score,
            $this->work_quality_score,
            $this->productivity_score,
            $this->communication_score,
            $this->teamwork_score,
            $this->initiative_score,
            $this->attendance_score,
            $this->leadership_score,
            $this->goal_achievement_score,
        ]);
        return count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : 0;
    }

    public function determineRating(): string {
        $score = $this->overall_score ?? 0;
        return match(true) {
            $score >= 4.5 => 'outstanding',
            $score >= 3.5 => 'exceeds_expectations',
            $score >= 2.5 => 'meets_expectations',
            $score >= 1.5 => 'needs_improvement',
            default       => 'unsatisfactory',
        };
    }
}