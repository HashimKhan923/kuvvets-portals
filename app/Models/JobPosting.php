<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPosting extends Model {
    use SoftDeletes;

    protected $fillable = [
        'company_id','department_id','designation_id','created_by',
        'title','reference_no','description','requirements','responsibilities',
        'type','experience_level','vacancies','salary_min','salary_max',
        'salary_disclosed','location','posted_date','deadline','status',
        'total_applications',
    ];

    protected $casts = [
        'posted_date'      => 'date',
        'deadline'         => 'date',
        'salary_disclosed' => 'boolean',
        'salary_min'       => 'decimal:2',
        'salary_max'       => 'decimal:2',
    ];

    public function company()     { return $this->belongsTo(Company::class); }
    public function department()  { return $this->belongsTo(Department::class); }
    public function designation() { return $this->belongsTo(Designation::class); }
    public function creator()     { return $this->belongsTo(User::class, 'created_by'); }
    public function applicants()  { return $this->hasMany(Applicant::class); }
    public function interviews()  { return $this->hasMany(Interview::class); }
    public function offerLetters(){ return $this->hasMany(OfferLetter::class); }

    public function getStatusBadgeAttribute(): array {
        return match($this->status) {
            'open'      => ['bg'=>'#0a1a0a','color'=>'#4CAF50','border'=>'#1a3a0a'],
            'draft'     => ['bg'=>'#111820','color'=>'#7a6a50','border'=>'#1e2a35'],
            'on_hold'   => ['bg'=>'#1a1200','color'=>'#EF9F27','border'=>'#2a2008'],
            'closed'    => ['bg'=>'#001015','color'=>'#378ADD','border'=>'#0a2a35'],
            'cancelled' => ['bg'=>'#1a0505','color'=>'#E24B4A','border'=>'#3a1010'],
            default     => ['bg'=>'#111820','color'=>'#7a6a50','border'=>'#1e2a35'],
        };
    }

    public function isExpired(): bool {
        return $this->deadline && $this->deadline->isPast();
    }

    public function getDaysRemainingAttribute(): ?int {
        if (!$this->deadline) return null;
        return max(0, now()->diffInDays($this->deadline, false));
    }

    public function getSalaryRangeAttribute(): string {
        if (!$this->salary_disclosed) return 'Confidential';
        if ($this->salary_min && $this->salary_max)
            return 'PKR ' . number_format($this->salary_min) . ' – ' . number_format($this->salary_max);
        if ($this->salary_min)
            return 'PKR ' . number_format($this->salary_min) . '+';
        return 'Negotiable';
    }
}