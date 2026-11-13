<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DatabaseBackup extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'disk',
        'file_path',
        'file_size',
        'campus_id',
        'type',
        'status',
        'gdrive_status',
        'gdrive_folder_id',
        'gdrive_file_id',
        'gdrive_file_url',
        'error_message',
        'created_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Dapatkan ukuran file dalam format yang mudah dibaca (KB / MB).
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }

    /**
     * Cek apakah file fisik masih tersimpan di penyimpanan lokal.
     */
    public function existsLocally(): bool
    {
        return Storage::disk($this->disk)->exists($this->file_path);
    }

    /**
     * Dapatkan tautan web folder Google Drive tujuan.
     */
    public function getGdriveFolderUrlAttribute(): ?string
    {
        if (empty($this->gdrive_folder_id)) {
            return null;
        }

        return "https://drive.google.com/drive/folders/{$this->gdrive_folder_id}";
    }

    /**
     * Badge HTML status lokal.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'success' => '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200"><i data-lucide="check" class="w-3 h-3 text-emerald-600"></i><span>Berhasil</span></span>',
            'failed' => '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200"><i data-lucide="alert-circle" class="w-3 h-3 text-rose-600"></i><span>Gagal</span></span>',
            default => '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200"><i data-lucide="clock" class="w-3 h-3 text-amber-600"></i><span>Memproses</span></span>',
        };
    }

    /**
     * Data array badge status lokal.
     */
    public function getStatusBadgeDataAttribute(): array
    {
        return match ($this->status) {
            'success' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Berhasil'],
            'failed' => ['bg' => 'bg-rose-50 text-rose-700 border-rose-200', 'label' => 'Gagal'],
            default => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => 'Memproses'],
        };
    }

    /**
     * Badge HTML status Google Drive.
     */
    public function getGdriveStatusBadgeAttribute(): string
    {
        return match ($this->gdrive_status) {
            'uploaded' => '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-sky-50 text-sky-700 border border-sky-200"><i data-lucide="cloud-check" class="w-3 h-3 text-sky-600"></i><span>Tersinkron GDrive</span></span>',
            'failed' => '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200"><i data-lucide="alert-triangle" class="w-3 h-3 text-rose-600"></i><span>Gagal Upload GDrive</span></span>',
            'not_configured' => '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-600 border border-slate-200"><i data-lucide="cloud-off" class="w-3 h-3 text-slate-500"></i><span>Lokal Saja</span></span>',
            default => '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200"><i data-lucide="clock" class="w-3 h-3 text-amber-600"></i><span>Menunggu Upload</span></span>',
        };
    }

    /**
     * Data array badge status Google Drive.
     */
    public function getGdriveBadgeAttribute(): array
    {
        return match ($this->gdrive_status) {
            'uploaded' => ['bg' => 'bg-sky-50 text-sky-700 border-sky-200', 'label' => 'Tersinkron GDrive', 'icon' => 'check-circle-2'],
            'failed' => ['bg' => 'bg-rose-50 text-rose-700 border-rose-200', 'label' => 'Gagal Upload GDrive', 'icon' => 'alert-triangle'],
            'not_configured' => ['bg' => 'bg-slate-100 text-slate-600 border-slate-200', 'label' => 'Lokal Saja (GDrive Belum Diatur)', 'icon' => 'cloud-off'],
            default => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => 'Menunggu Upload', 'icon' => 'clock'],
        };
    }
}

