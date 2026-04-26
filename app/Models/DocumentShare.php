<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentShare extends Model {
    protected $fillable = [
        'document_id', 'shared_with', 'shared_by',
        'permission', 'expires_at',
    ];

    protected $casts = ['expires_at' => 'datetime'];

    public function document()    { return $this->belongsTo(Document::class); }
    public function sharedWith()  { return $this->belongsTo(User::class, 'shared_with'); }
    public function sharedBy()    { return $this->belongsTo(User::class, 'shared_by'); }
}