<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model {
    protected $fillable = [
        'employee_id','title','type','file_path','file_name',
        'file_size','issue_date','expiry_date','is_verified','notes','uploaded_by',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'expiry_date' => 'date',
        'is_verified' => 'boolean',
    ];

    public function employee()   { return $this->belongsTo(Employee::class); }
    public function uploader()   { return $this->belongsTo(User::class, 'uploaded_by'); }

    public function getFileUrlAttribute(): string {
        return asset('storage/' . $this->file_path);
    }

    public function isExpiringSoon(): bool {
        return $this->expiry_date && $this->expiry_date->diffInDays(now()) <= 30
               && $this->expiry_date->isFuture();
    }

    public function isExpired(): bool {
        return $this->expiry_date && $this->expiry_date->isPast();
    }
}