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
            'scheduled'   => ['bg'=>'#1a1200','color'=>'#EF9F27','border'=>'#2a2008'],
            'completed'   => ['bg'=>'#0a1a0a','color'=>'#4CAF50','border'=>'#1a3a0a'],
            'cancelled'   => ['bg'=>'#1a0505','color'=>'#E24B4A','border'=>'#3a1010'],
            'no_show'     => ['bg'=>'#1a0505','color'=>'#E24B4A','border'=>'#3a1010'],
            'rescheduled' => ['bg'=>'#001015','color'=>'#378ADD','border'=>'#0a2a35'],
            default       => ['bg'=>'#111820','color'=>'#7a6a50','border'=>'#1e2a35'],
        };
    }
}