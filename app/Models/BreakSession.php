<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BreakSession extends Model
{
    protected $fillable = [
        'attendance_id','employee_id','started_at','ended_at',
        'duration_minutes','reason','notes',
    ];

    protected $casts = [
        'started_at'       => 'datetime',
        'ended_at'         => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public function attendance() { return $this->belongsTo(Attendance::class); }
    public function employee()   { return $this->belongsTo(Employee::class); }

    public function isActive(): bool
    {
        return $this->started_at && !$this->ended_at;
    }

    public static function reasonLabel(?string $reason): string
    {
        return match($reason) {
            'lunch'    => 'Lunch',
            'prayer'   => 'Prayer',
            'tea'      => 'Tea break',
            'personal' => 'Personal',
            default    => 'Break',
        };
    }

    public static function reasonIcon(?string $reason): string
    {
        return match($reason) {
            'lunch'    => 'fa-utensils',
            'prayer'   => 'fa-mosque',
            'tea'      => 'fa-mug-hot',
            'personal' => 'fa-user-clock',
            default    => 'fa-pause',
        };
    }
}