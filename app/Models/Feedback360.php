<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback360 extends Model {
    protected $table = 'feedback_360';

    protected $fillable = [
        'appraisal_id', 'employee_id', 'reviewer_id',
        'relationship', 'score', 'feedback',
        'is_anonymous', 'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'is_anonymous' => 'boolean',
    ];

    public function appraisal() { return $this->belongsTo(Appraisal::class); }
    public function employee()  { return $this->belongsTo(Employee::class); }
    public function reviewer()  { return $this->belongsTo(User::class, 'reviewer_id'); }
}