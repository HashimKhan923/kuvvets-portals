<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Document extends Model {
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'document_category_id', 'employee_id', 'uploaded_by',
        'title', 'document_number', 'description', 'file_path', 'file_name',
        'file_type', 'file_size', 'type', 'access_level', 'status',
        'issue_date', 'expiry_date', 'version', 'parent_document_id',
        'is_latest_version', 'download_count', 'view_count', 'tags',
    ];

    protected $casts = [
        'issue_date'        => 'date',
        'expiry_date'       => 'date',
        'is_latest_version' => 'boolean',
    ];

    public function company()   { return $this->belongsTo(Company::class); }
    public function category()  { return $this->belongsTo(DocumentCategory::class, 'document_category_id'); }
    public function employee()  { return $this->belongsTo(Employee::class); }
    public function uploader()  { return $this->belongsTo(User::class, 'uploaded_by'); }
    public function parent()    { return $this->belongsTo(Document::class, 'parent_document_id'); }
    public function versions()  { return $this->hasMany(Document::class, 'parent_document_id'); }
    public function shares()    { return $this->hasMany(DocumentShare::class); }

    public function getFileSizeFormattedAttribute(): string {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    public function getFileIconAttribute(): string {
        return match(strtolower($this->file_type ?? '')) {
            'pdf'                   => 'fa-file-pdf',
            'doc', 'docx'           => 'fa-file-word',
            'xls', 'xlsx'           => 'fa-file-excel',
            'ppt', 'pptx'           => 'fa-file-powerpoint',
            'jpg', 'jpeg', 'png',
            'gif', 'webp'           => 'fa-file-image',
            'zip', 'rar', '7z'      => 'fa-file-zipper',
            default                 => 'fa-file',
        };
    }

    public function getFileIconColorAttribute(): string {
        return match(strtolower($this->file_type ?? '')) {
            'pdf'                   => '#C53030',
            'doc', 'docx'           => '#2B6CB0',
            'xls', 'xlsx'           => '#2D7A4F',
            'ppt', 'pptx'           => '#B7791F',
            'jpg', 'jpeg', 'png',
            'gif', 'webp'           => '#6B46C1',
            'zip', 'rar', '7z'      => '#718096',
            default                 => '#C49A3C',
        };
    }

    public function getStatusBadgeAttribute(): array {
        return match($this->status) {
            'active'   => ['bg' => '#F0FBF4', 'color' => '#2D7A4F', 'border' => '#B8E4CA'],
            'expired'  => ['bg' => '#FFF5F5', 'color' => '#C53030', 'border' => '#FEB2B2'],
            'archived' => ['bg' => '#F7FAFC', 'color' => '#718096', 'border' => '#E2E8F0'],
            'draft'    => ['bg' => '#FFFBEB', 'color' => '#B7791F', 'border' => '#F6E05E'],
            default    => ['bg' => '#F7FAFC', 'color' => '#718096', 'border' => '#E2E8F0'],
        };
    }

    public function getTypeBadgeAttribute(): array {
        return match($this->type) {
            'policy'      => ['bg' => '#EBF8FF', 'color' => '#2B6CB0', 'border' => '#BEE3F8'],
            'procedure'   => ['bg' => '#F0FBF4', 'color' => '#2D7A4F', 'border' => '#B8E4CA'],
            'contract'    => ['bg' => '#FBF5E6', 'color' => '#8B6914', 'border' => '#E8D5A3'],
            'certificate' => ['bg' => '#F0FFF4', 'color' => '#276749', 'border' => '#9AE6B4'],
            'compliance'  => ['bg' => '#FFF5F5', 'color' => '#C53030', 'border' => '#FEB2B2'],
            'hr_document' => ['bg' => '#FAF5FF', 'color' => '#6B46C1', 'border' => '#D6BCFA'],
            'legal'       => ['bg' => '#FFFBEB', 'color' => '#B7791F', 'border' => '#F6E05E'],
            'financial'   => ['bg' => '#F0FBF4', 'color' => '#276749', 'border' => '#9AE6B4'],
            'training'    => ['bg' => '#EBF8FF', 'color' => '#2B6CB0', 'border' => '#BEE3F8'],
            default       => ['bg' => '#F7FAFC', 'color' => '#718096', 'border' => '#E2E8F0'],
        };
    }

    public function isExpired(): bool {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(): bool {
        return $this->expiry_date
            && $this->expiry_date->isFuture()
            && $this->expiry_date->diffInDays(now()) <= 30;
    }

    public function getDownloadUrlAttribute(): string {
        return route('documents.download', $this);
    }
}