<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model {
    protected $fillable = [
        'company_id', 'name', 'category', 'description', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function company()       { return $this->belongsTo(Company::class); }
    public function employeeSkills(){ return $this->hasMany(EmployeeSkill::class); }
}