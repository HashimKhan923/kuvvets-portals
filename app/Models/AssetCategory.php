<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model {
    protected $fillable = [
        'company_id', 'name', 'code', 'description',
        'icon', 'color', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function company() { return $this->belongsTo(Company::class); }
    public function assets()  { return $this->hasMany(Asset::class, 'asset_category_id'); }
}