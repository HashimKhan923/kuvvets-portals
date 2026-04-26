<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model {
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'parent_id', 'name', 'code', 'description', 'cost_center', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function company()      { return $this->belongsTo(Company::class); }
    public function parent()       { return $this->belongsTo(Department::class, 'parent_id'); }
    public function children()     { return $this->hasMany(Department::class, 'parent_id'); }
    public function employees()    { return $this->hasMany(Employee::class); }
    public function designations() { return $this->hasMany(Designation::class); }

    public function getFullNameAttribute(): string {
        return $this->parent
            ? $this->parent->name . ' → ' . $this->name
            : $this->name;
    }
}