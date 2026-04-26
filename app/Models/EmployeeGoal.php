<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeGoal extends Model {
    protected $fillable = [
        'employee_id', 'performance_cycle_id', 'kpi_id', 'assigned_by',
        'title', 'description', 'category',
        'target_value', 'achieved_value', 'unit',
        'weight', 'due_date', 'status', 'progress',
        'employee_comments', 'manager_comments',
    ];

    protected $casts = [
        'due_date'       => 'date',
        'target_value'   => 'decimal:2',
        'achieved_value' => 'decimal:2',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }
    public function cycle()    { return $this->belongsTo(PerformanceCycle::class, 'performance_cycle_id'); }
    public function kpi()      { return $this->belongsTo(Kpi::class); }
    public function assigner() { return $this->belongsTo(User::class, 'assigned_by'); }

    public function getStatusBadgeAttribute(): array {
        return match($this->status) {
            'not_started' => ['bg' => '#F7FAFC', 'color' => '#718096', 'border' => '#E2E8F0'],
            'in_progress' => ['bg' => '#FFFBEB', 'color' => '#B7791F', 'border' => '#F6E05E'],
            'completed'   => ['bg' => '#F0FBF4', 'color' => '#2D7A4F', 'border' => '#B8E4CA'],
            'cancelled'   => ['bg' => '#FFF5F5', 'color' => '#C53030', 'border' => '#FEB2B2'],
            default       => ['bg' => '#F7FAFC', 'color' => '#718096', 'border' => '#E2E8F0'],
        };
    }

    public function getAchievementPercentAttribute(): float {
        if (!$this->target_value || $this->target_value == 0) return 0;
        return min(100, round(($this->achieved_value / $this->target_value) * 100, 1));
    }
}