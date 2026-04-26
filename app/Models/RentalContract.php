<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalContract extends Model {
    protected $fillable = [
        'asset_id', 'company_id', 'contract_number', 'rental_type',
        'party_name', 'party_contact', 'start_date', 'end_date',
        'rate_per_day', 'total_amount', 'deposit_amount', 'status',
        'terms', 'document_path', 'created_by',
    ];

    protected $casts = [
        'start_date'    => 'date',
        'end_date'      => 'date',
        'rate_per_day'  => 'decimal:2',
        'total_amount'  => 'decimal:2',
        'deposit_amount'=> 'decimal:2',
    ];

    public function asset()   { return $this->belongsTo(Asset::class); }
    public function company() { return $this->belongsTo(Company::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public function getStatusBadgeAttribute(): array {
        return match($this->status) {
            'active'    => ['bg'=>'#F0FBF4','color'=>'#2D7A4F','border'=>'#B8E4CA'],
            'completed' => ['bg'=>'#EBF8FF','color'=>'#2B6CB0','border'=>'#BEE3F8'],
            'cancelled' => ['bg'=>'#FFF5F5','color'=>'#C53030','border'=>'#FEB2B2'],
            'overdue'   => ['bg'=>'#FFFBEB','color'=>'#B7791F','border'=>'#F6E05E'],
            default     => ['bg'=>'#F7FAFC','color'=>'#718096','border'=>'#E2E8F0'],
        };
    }

    public function getDurationDaysAttribute(): int {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function isExpiringSoon(): bool {
        return $this->status === 'active'
            && $this->end_date->isFuture()
            && $this->end_date->diffInDays(now()) <= 7;
    }
}