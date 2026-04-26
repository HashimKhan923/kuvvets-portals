<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model {
    protected $fillable = [
        'company_id', 'name', 'date', 'date_to',
        'type', 'is_recurring', 'description', 'year',
    ];

    protected $casts = [
        'date'         => 'date',
        'date_to'      => 'date',
        'is_recurring' => 'boolean',
    ];

    public function company() { return $this->belongsTo(Company::class); }

    public function getTypeBadgeAttribute(): array {
        return match($this->type) {
            'national'  => ['bg'=>'#001015', 'color'=>'#378ADD', 'border'=>'#0a2a35'],
            'religious' => ['bg'=>'#1a1200', 'color'=>'#EF9F27', 'border'=>'#2a2008'],
            'company'   => ['bg'=>'#0a1a0a', 'color'=>'#4CAF50', 'border'=>'#1a3a0a'],
            'optional'  => ['bg'=>'#100a1a', 'color'=>'#7F77DD', 'border'=>'#2a1a3a'],
            default     => ['bg'=>'#111820', 'color'=>'#7a6a50', 'border'=>'#1e2a35'],
        };
    }

    public function getDaysCountAttribute(): int {
        if (!$this->date_to) return 1;
        return $this->date->diffInDays($this->date_to) + 1;
    }
}