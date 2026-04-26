<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeShift extends Model {
    protected $fillable = [
        'employee_id','shift_id','effective_from','effective_to','is_current',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to'   => 'date',
        'is_current'     => 'boolean',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }
    public function shift()    { return $this->belongsTo(Shift::class); }
}