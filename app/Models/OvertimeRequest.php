<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OvertimeRequest extends Model {
    protected $fillable = [
        'employee_id','attendance_id','date','requested_minutes',
        'approved_minutes','reason','status','approved_by',
        'approved_at','rejection_reason',
    ];

    protected $casts = [
        'date'        => 'date',
        'approved_at' => 'datetime',
    ];

    public function employee()   { return $this->belongsTo(Employee::class); }
    public function attendance() { return $this->belongsTo(Attendance::class); }
    public function approver()   { return $this->belongsTo(User::class, 'approved_by'); }
}