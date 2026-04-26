<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model {
    protected $fillable = [
        'company_id','name','code','start_time','end_time',
        'grace_minutes','break_minutes','working_hours',
        'is_night_shift','is_active','working_days',
    ];

    protected $casts = [
        'working_days'  => 'array',
        'is_night_shift'=> 'boolean',
        'is_active'     => 'boolean',
    ];

    public function company()   { return $this->belongsTo(Company::class); }
    public function employees() { return $this->hasMany(EmployeeShift::class); }
    public function attendances(){ return $this->hasMany(Attendance::class); }

    public function getTimingAttribute(): string {
        return date('h:i A', strtotime($this->start_time))
             . ' – '
             . date('h:i A', strtotime($this->end_time));
    }
}