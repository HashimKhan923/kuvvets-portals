<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetAssignment extends Model {
    protected $fillable = [
        'asset_id', 'employee_id', 'assigned_by',
        'assigned_date', 'expected_return_date', 'actual_return_date',
        'status', 'purpose', 'condition_on_issue',
        'condition_on_return', 'notes', 'returned_to',
    ];

    protected $casts = [
        'assigned_date'        => 'date',
        'expected_return_date' => 'date',
        'actual_return_date'   => 'date',
    ];

    public function asset()      { return $this->belongsTo(Asset::class); }
    public function employee()   { return $this->belongsTo(Employee::class); }
    public function assigner()   { return $this->belongsTo(User::class, 'assigned_by'); }
    public function receiver()   { return $this->belongsTo(User::class, 'returned_to'); }

    public function isOverdue(): bool {
        return $this->status === 'active'
            && $this->expected_return_date
            && $this->expected_return_date->isPast();
    }
}