<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DocumentCategory extends Model {
    protected $fillable = [
        'company_id', 'name', 'slug', 'description',
        'icon', 'color', 'requires_expiry',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'requires_expiry' => 'boolean',
        'is_active'       => 'boolean',
    ];

    public function company()   { return $this->belongsTo(Company::class); }
    public function documents() { return $this->hasMany(Document::class, 'document_category_id'); }

    protected static function boot() {
        parent::boot();
        static::creating(function ($cat) {
            $cat->slug = $cat->slug ?? Str::slug($cat->name);
        });
    }
}