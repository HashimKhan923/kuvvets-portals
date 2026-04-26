<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Applicant extends Model {
    use SoftDeletes;

    protected $fillable = [
        'company_id','job_posting_id','first_name','last_name','email','phone',
        'cnic','total_experience_years','current_employer','current_designation',
        'current_salary','expected_salary','notice_period_days','cv_path',
        'cover_letter_path','city','source','referred_by','stage','rating',
        'notes','assigned_to',
    ];

    protected $casts = [
        'current_salary'  => 'decimal:2',
        'expected_salary' => 'decimal:2',
    ];

    public function jobPosting()  { return $this->belongsTo(JobPosting::class); }
    public function assignedTo()  { return $this->belongsTo(User::class, 'assigned_to'); }
    public function interviews()  { return $this->hasMany(Interview::class); }
    public function offerLetters(){ return $this->hasMany(OfferLetter::class); }

    public function getFullNameAttribute(): string {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getCvUrlAttribute(): ?string {
        return $this->cv_path ? asset('storage/' . $this->cv_path) : null;
    }

    public function getAvatarUrlAttribute(): string {
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name)
             . '&background=1a1200&color=BA7517&bold=true';
    }

    public function getStageBadgeAttribute(): array {
        return match($this->stage) {
            'applied'              => ['bg'=>'#111820','color'=>'#7a6a50','border'=>'#1e2a35',  'label'=>'Applied'],
            'screening'            => ['bg'=>'#001015','color'=>'#378ADD','border'=>'#0a2a35',  'label'=>'Screening'],
            'shortlisted'          => ['bg'=>'#1a1200','color'=>'#EF9F27','border'=>'#2a2008',  'label'=>'Shortlisted'],
            'interview_scheduled'  => ['bg'=>'#100a1a','color'=>'#7F77DD','border'=>'#2a1a3a',  'label'=>'Interview Scheduled'],
            'interviewed'          => ['bg'=>'#0a1015','color'=>'#1D9E75','border'=>'#0a2a20',  'label'=>'Interviewed'],
            'assessment'           => ['bg'=>'#1a0a10','color'=>'#D4537E','border'=>'#3a1020',  'label'=>'Assessment'],
            'offer_sent'           => ['bg'=>'#1a1200','color'=>'#BA7517','border'=>'#2a2008',  'label'=>'Offer Sent'],
            'offer_accepted'       => ['bg'=>'#0a1a0a','color'=>'#4CAF50','border'=>'#1a3a0a',  'label'=>'Offer Accepted'],
            'offer_declined'       => ['bg'=>'#1a0505','color'=>'#E24B4A','border'=>'#3a1010',  'label'=>'Offer Declined'],
            'hired'                => ['bg'=>'#0a1a0a','color'=>'#4CAF50','border'=>'#1a3a0a',  'label'=>'Hired ✓'],
            'rejected'             => ['bg'=>'#1a0505','color'=>'#E24B4A','border'=>'#3a1010',  'label'=>'Rejected'],
            'withdrawn'            => ['bg'=>'#111820','color'=>'#5a5040','border'=>'#1e2a35',  'label'=>'Withdrawn'],
            default                => ['bg'=>'#111820','color'=>'#7a6a50','border'=>'#1e2a35',  'label'=>ucfirst($this->stage)],
        };
    }

    public function getRatingStarsAttribute(): string {
        if (!$this->rating) return '—';
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }
}