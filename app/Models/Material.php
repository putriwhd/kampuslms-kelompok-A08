<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'uploaded_by',
        'title',
        'description',
        'type',
        'file_path',
        'original_name',
        'file_size',
        'mime_type',
        'external_url',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    /**
     * Event booted Eloquent: menghapus berkas fisik di disk privat saat record materi dihapus.
     */
    protected static function booted(): void
    {
        static::deleting(function (Material $material) {
            if ($material->file_path && Storage::disk('local')->exists($material->file_path)) {
                Storage::disk('local')->delete($material->file_path);
            }
        });
    }

    /**
     * Accessor untuk menyajikan ukuran berkas dalam format ramah manusia (B/KB/MB).
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / pow(1024, $power);

        return number_format($value, 2) . ' ' . $units[$power];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}