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
        'applied'              => ['bg'=>'#fffbeb','color'=>'#7a6a50','border'=>'#fde68a','label'=>'Applied'],
        'screening'            => ['bg'=>'#fffbeb','color'=>'#378ADD','border'=>'#fde68a','label'=>'Screening'],
        'shortlisted'          => ['bg'=>'#fffbeb','color'=>'#CBA557','border'=>'#fde68a','label'=>'Shortlisted'],
        'interview_scheduled'  => ['bg'=>'#fffbeb','color'=>'#7F77DD','border'=>'#fde68a','label'=>'Interview Scheduled'],
        'interviewed'          => ['bg'=>'#fffbeb','color'=>'#1D9E75','border'=>'#fde68a','label'=>'Interviewed'],
        'assessment'           => ['bg'=>'#fffbeb','color'=>'#D4537E','border'=>'#fde68a','label'=>'Assessment'],
        'offer_sent'           => ['bg'=>'#fffbeb','color'=>'#CBA557','border'=>'#fde68a','label'=>'Offer Sent'],
        'offer_accepted'       => ['bg'=>'#fffbeb','color'=>'#4CAF50','border'=>'#fde68a','label'=>'Offer Accepted'],
        'offer_declined'       => ['bg'=>'#fffbeb','color'=>'#E24B4A','border'=>'#fde68a','label'=>'Offer Declined'],
        'hired'                => ['bg'=>'#fffbeb','color'=>'#4CAF50','border'=>'#fde68a','label'=>'Hired ✓'],
        'rejected'             => ['bg'=>'#fffbeb','color'=>'#E24B4A','border'=>'#fde68a','label'=>'Rejected'],
        'withdrawn'            => ['bg'=>'#fffbeb','color'=>'#5a5040','border'=>'#fde68a','label'=>'Withdrawn'],
        default                => ['bg'=>'#fffbeb','color'=>'#7a6a50','border'=>'#fde68a','label'=>ucfirst($this->stage)],
    };
}

    public function getRatingStarsAttribute(): string {
        if (!$this->rating) return '—';
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }
}