<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCertification extends Model {
    protected $fillable = [
        'employee_id', 'training_enrollment_id', 'certificate_name',
        'issued_by', 'certificate_number', 'issue_date',
        'expiry_date', 'document_path', 'is_verified', 'verified_by',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'expiry_date' => 'date',
        'is_verified' => 'boolean',
    ];

    public function employee()   { return $this->belongsTo(Employee::class); }
    public function enrollment() { return $this->belongsTo(TrainingEnrollment::class, 'training_enrollment_id'); }
    public function verifier()   { return $this->belongsTo(User::class, 'verified_by'); }

    public function isExpired(): bool {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(): bool {
        return $this->expiry_date
            && $this->expiry_date->isFuture()
            && $this->expiry_date->diffInDays(now()) <= 30;
    }

    public function getExpiryStatusAttribute(): array {
        if (!$this->expiry_date)
            return ['label' => 'No Expiry', 'color' => '#718096', 'bg' => '#F7FAFC'];
        if ($this->isExpired())
            return ['label' => 'Expired', 'color' => '#C53030', 'bg' => '#FFF5F5'];
        if ($this->isExpiringSoon())
            return ['label' => 'Expiring Soon', 'color' => '#B7791F', 'bg' => '#FFFBEB'];
        return ['label' => 'Valid', 'color' => '#2D7A4F', 'bg' => '#F0FBF4'];
    }
}