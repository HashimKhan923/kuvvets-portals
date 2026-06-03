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
        'national'  => ['bg'=>'#fffbeb','color'=>'#378ADD','border'=>'#fde68a'],
        'religious' => ['bg'=>'#fffbeb','color'=>'#CBA557','border'=>'#fde68a'],
        'company'   => ['bg'=>'#fffbeb','color'=>'#4CAF50','border'=>'#fde68a'],
        'optional'  => ['bg'=>'#fffbeb','color'=>'#7F77DD','border'=>'#fde68a'],
        default     => ['bg'=>'#fffbeb','color'=>'#7a6a50','border'=>'#fde68a'],
    };
}

    public function getDaysCountAttribute(): int {
        if (!$this->date_to) return 1;
        return $this->date->diffInDays($this->date_to) + 1;
    }
}