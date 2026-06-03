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
            'open'      => ['bg'=>'#fffbeb','color'=>'#4CAF50','border'=>'#fde68a'],
            'draft'     => ['bg'=>'#fffbeb','color'=>'#7a6a50','border'=>'#fde68a'],
            'on_hold'   => ['bg'=>'#fffbeb','color'=>'#CBA557','border'=>'#fde68a'],
            'closed'    => ['bg'=>'#fffbeb','color'=>'#378ADD','border'=>'#fde68a'],
            'cancelled' => ['bg'=>'#fffbeb','color'=>'#E24B4A','border'=>'#fde68a'],
            default     => ['bg'=>'#fffbeb','color'=>'#7a6a50','border'=>'#fde68a'],
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