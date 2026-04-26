<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSkill extends Model {
    protected $table = 'employee_skills';

    protected $fillable = [
        'employee_id', 'skill_id', 'level', 'rating', 'last_assessed',
    ];

    protected $casts = ['last_assessed' => 'date'];

    public function employee() { return $this->belongsTo(Employee::class); }
    public function skill()    { return $this->belongsTo(Skill::class); }

    public function getLevelColorAttribute(): string {
        return match($this->level) {
            'beginner'     => '#B7791F',
            'intermediate' => '#2B6CB0',
            'advanced'     => '#2D7A4F',
            'expert'       => '#276749',
            default        => '#718096',
        };
    }
}