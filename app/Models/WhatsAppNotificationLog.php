<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppNotificationLog extends Model
{
    protected $table = 'whatsapp_notification_logs';

    public const TYPE_PROPOSAL_MASUK = 'proposal_masuk';
    public const TYPE_KUNJUNGAN_H1 = 'kunjungan_h1';
    public const TYPE_SOSIALISASI_H5 = 'sosialisasi_h5';
    public const TYPE_EVENT_H7 = 'event_h7';
    public const TYPE_TEST = 'test';

    public const STATUS_TERKIRIM = 'terkirim';
    public const STATUS_GAGAL = 'gagal';
    public const STATUS_SIMULASI = 'simulasi';

    protected $fillable = [
        'notification_type',
        'reference_type',
        'reference_id',
        'recipient_phone',
        'recipient_name',
        'message',
        'status',
        'response_payload',
        'calendar_url',
        'email_recipients',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * Human-readable label for notification types.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->notification_type) {
            self::TYPE_PROPOSAL_MASUK => 'Proposal Baru Masuk (H-0)',
            self::TYPE_KUNJUNGAN_H1 => 'Agenda Kunjungan (H-1)',
            self::TYPE_SOSIALISASI_H5 => 'Jadwal Sosialisasi (H-5)',
            self::TYPE_EVENT_H7 => 'Pelaksanaan Event (H-7)',
            self::TYPE_TEST => 'Pesan Uji Coba (Test)',
            default => ucfirst(str_replace('_', ' ', $this->notification_type)),
        };
    }

    /**
     * Badge CSS class for notification types.
     */
    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->notification_type) {
            self::TYPE_PROPOSAL_MASUK => 'bg-amber-100 text-amber-800 border-amber-300',
            self::TYPE_KUNJUNGAN_H1 => 'bg-blue-100 text-blue-800 border-blue-300',
            self::TYPE_SOSIALISASI_H5 => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            self::TYPE_EVENT_H7 => 'bg-purple-100 text-purple-800 border-purple-300',
            default => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }

    /**
     * Badge CSS class for status.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_TERKIRIM => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            self::STATUS_GAGAL => 'bg-rose-100 text-rose-800 border-rose-300',
            self::STATUS_SIMULASI => 'bg-indigo-100 text-indigo-800 border-indigo-300',
            default => 'bg-slate-100 text-slate-700 border-slate-300',
        };
    }

    /**
     * Direct WhatsApp click link for manual sending or fallback.
     */
    public function getDirectWaLinkAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->recipient_phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        return "https://wa.me/{$phone}?text=" . urlencode($this->message);
    }
}
