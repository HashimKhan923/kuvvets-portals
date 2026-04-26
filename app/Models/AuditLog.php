<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model {
    public $timestamps = false;
    protected $fillable = [
        'user_id','event','auditable_type','auditable_id',
        'old_values','new_values','ip_address','user_agent','url','tags',
    ];
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function auditable() { return $this->morphTo(); }

    public static function log(string $event, $model = null, array $old = [], array $new = []): void {
        static::create([
            'user_id'         => auth()->id(),
            'event'           => $event,
            'auditable_type'  => $model ? get_class($model) : null,
            'auditable_id'    => $model?->id,
            'old_values'      => $old,
            'new_values'      => $new,
            'ip_address'      => request()->ip(),
            'user_agent'      => request()->userAgent(),
            'url'             => request()->fullUrl(),
            'created_at'      => now(),
        ]);
    }
}