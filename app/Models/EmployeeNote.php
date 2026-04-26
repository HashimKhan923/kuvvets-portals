<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeNote extends Model {
    protected $fillable = [
        'employee_id','created_by','title','body','type','is_private',
    ];

    protected $casts = ['is_private' => 'boolean'];

    public function employee()  { return $this->belongsTo(Employee::class); }
    public function author()    { return $this->belongsTo(User::class, 'created_by'); }

    public function getTypeBadgeAttribute(): array {
        return match($this->type) {
            'warning'      => ['bg' => '#1a0505', 'color' => '#E24B4A', 'border' => '#3a1010', 'label' => 'Warning'],
            'commendation' => ['bg' => '#0a1a0a', 'color' => '#4CAF50', 'border' => '#1a3a0a', 'label' => 'Commendation'],
            'hr_note'      => ['bg' => '#1a1200', 'color' => '#EF9F27', 'border' => '#2a2008', 'label' => 'HR Note'],
            'performance'  => ['bg' => '#001015', 'color' => '#378ADD', 'border' => '#0a2a35', 'label' => 'Performance'],
            default        => ['bg' => '#111820', 'color' => '#7a6a50', 'border' => '#1e2a35', 'label' => 'General'],
        };
    }
}