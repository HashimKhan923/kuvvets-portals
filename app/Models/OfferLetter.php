<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferLetter extends Model {
    protected $fillable = [
        'applicant_id','job_posting_id','created_by','offer_number',
        'offered_salary','joining_date','offer_expiry','status',
        'terms','pdf_path','accepted_at','declined_at','decline_reason',
    ];

    protected $casts = [
        'joining_date'  => 'date',
        'offer_expiry'  => 'date',
        'accepted_at'   => 'datetime',
        'declined_at'   => 'datetime',
        'offered_salary'=> 'decimal:2',
    ];

    public function applicant()  { return $this->belongsTo(Applicant::class); }
    public function jobPosting() { return $this->belongsTo(JobPosting::class); }
    public function creator()    { return $this->belongsTo(User::class, 'created_by'); }

    public function isExpired(): bool {
        return $this->offer_expiry->isPast() && $this->status === 'sent';
    }
}