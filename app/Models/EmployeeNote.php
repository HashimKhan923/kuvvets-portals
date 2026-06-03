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
        'warning'      => ['bg'=>'#fffbeb','color'=>'#E24B4A','border'=>'#fde68a','label'=>'Warning'],
        'commendation' => ['bg'=>'#fffbeb','color'=>'#4CAF50','border'=>'#fde68a','label'=>'Commendation'],
        'hr_note'      => ['bg'=>'#fffbeb','color'=>'#CBA557','border'=>'#fde68a','label'=>'HR Note'],
        'performance'  => ['bg'=>'#fffbeb','color'=>'#378ADD','border'=>'#fde68a','label'=>'Performance'],
        default        => ['bg'=>'#fffbeb','color'=>'#7a6a50','border'=>'#fde68a','label'=>'General'],
    };
}
}