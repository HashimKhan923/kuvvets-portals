<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftAssignmentLog extends Model
{
    protected $fillable = [
        'employee_id',
        'shift_id',
        'old_shift_id',
        'assigned_by',
        'effective_from',
        'notes',
    ];

    protected $casts = [
        'effective_from' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function oldShift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'old_shift_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}