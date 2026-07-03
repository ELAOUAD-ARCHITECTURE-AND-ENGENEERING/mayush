<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SellerDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'document_type',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'file_size'   => 'integer',
    ];

    /**
     * Supported document types with human-readable labels.
     */
    public static array $types = [
        'contract'              => 'Signed MayushSeller Contract',
        'government_id'         => 'Government-Issued Photo ID',
        'business_registration' => 'Business Registration Documents',
        'certification'         => 'Professional Certification (Optional)',
    ];

    /**
     * Mandatory document types that must be uploaded before review.
     */
    public static array $mandatoryTypes = [
        'contract',
        'government_id',
        'business_registration',
    ];

    public function shop(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Returns the file size in a human-readable format (e.g., "2.5 MB").
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes < 1024) return "{$bytes} B";
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 2) . ' MB';
    }

    /**
     * Whether the document is an image (displayable inline).
     */
    public function isImage(): bool
    {
        return in_array($this->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    /**
     * Whether the document is a PDF.
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }
}
