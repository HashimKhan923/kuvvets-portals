<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Designation extends Model {
    protected $fillable = [
        'company_id', 'department_id', 'title', 'grade', 'level',
        'min_salary', 'max_salary', 'is_active',
    ];

    protected $casts = [
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    public function company()    { return $this->belongsTo(Company::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function employees()  { return $this->hasMany(Employee::class); }
}