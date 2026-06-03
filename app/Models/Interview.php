<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interview extends Model {
    protected $fillable = [
        'applicant_id','job_posting_id','scheduled_by','round','type',
        'scheduled_at','duration_minutes','location','interviewers',
        'status','score','feedback','recommendation',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'interviewers' => 'array',
    ];

    public function applicant()   { return $this->belongsTo(Applicant::class); }
    public function jobPosting()  { return $this->belongsTo(JobPosting::class); }
    public function scheduler()   { return $this->belongsTo(User::class, 'scheduled_by'); }

    public function getStatusBadgeAttribute(): array {
    return match($this->status) {
        'scheduled'   => ['bg'=>'#fffbeb','color'=>'#CBA557','border'=>'#fde68a'],
        'completed'   => ['bg'=>'#fffbeb','color'=>'#4CAF50','border'=>'#fde68a'],
        'cancelled'   => ['bg'=>'#fffbeb','color'=>'#E24B4A','border'=>'#fde68a'],
        'no_show'     => ['bg'=>'#fffbeb','color'=>'#E24B4A','border'=>'#fde68a'],
        'rescheduled' => ['bg'=>'#fffbeb','color'=>'#378ADD','border'=>'#fde68a'],
        default       => ['bg'=>'#fffbeb','color'=>'#7a6a50','border'=>'#fde68a'],
    };
}
}