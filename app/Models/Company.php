<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'legal_name', 'ntn', 'strn', 'registration_no',
        'email', 'phone', 'website', 'address', 'city', 'province',
        'country', 'logo', 'currency', 'timezone', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function departments() { return $this->hasMany(Department::class); }
    public function designations() { return $this->hasMany(Designation::class); }
    public function employees()    { return $this->hasMany(Employee::class); }
    public function users()        { return $this->hasMany(User::class); }

    public function getLogoUrlAttribute(): string {
        return $this->logo
            ? asset('storage/' . $this->logo)
            : asset('images/kuvvet-logo.png');
    }
}