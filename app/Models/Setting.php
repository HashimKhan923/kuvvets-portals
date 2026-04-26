<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model {
    protected $fillable = [
        'company_id', 'group', 'key', 'value',
        'type', 'label', 'description', 'is_public',
    ];

    protected $casts = ['is_public' => 'boolean'];

    public function company() { return $this->belongsTo(Company::class); }

    // ── Static helpers ────────────────────────────────────────
    public static function get(string $key, mixed $default = null, ?int $companyId = null): mixed
    {
        $companyId = $companyId ?? auth()->user()?->company_id;
        if (!$companyId) return $default;

        $cacheKey = "settings.{$companyId}.{$key}";

        $value = Cache::remember($cacheKey, 3600, function () use ($key, $companyId) {
            return static::where('company_id', $companyId)
                ->where('key', $key)
                ->value('value');
        });

        return $value ?? $default;
    }

    public static function set(string $key, mixed $value, string $group = 'general', ?int $companyId = null): void
    {
        $companyId = $companyId ?? auth()->user()?->company_id;
        if (!$companyId) return;

        static::updateOrCreate(
            ['company_id' => $companyId, 'key' => $key],
            ['value' => $value, 'group' => $group]
        );

        Cache::forget("settings.{$companyId}.{$key}");
    }

    public static function getGroup(string $group, ?int $companyId = null): array
    {
        $companyId = $companyId ?? auth()->user()?->company_id;
        if (!$companyId) return [];

        return static::where('company_id', $companyId)
            ->where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }
}