<?php

namespace App\Services;

use App\Models\FollowUp;
use App\Models\SchoolVisit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FollowUpSyncService
{
    /**
     * Synchronize a single field visit into a follow-up record for fixing socialization schedule.
     */
    public function syncVisit(SchoolVisit $visit): FollowUp
    {
        $visit->loadMissing(['school.primaryContact', 'school.contacts']);

        $followUp = FollowUp::where('visit_id', $visit->id)->first();

        $picPhone = null;
        if ($visit->school) {
            $picPhone = $visit->school->primaryContact?->phone 
                     ?? $visit->school->contacts->first()?->phone 
                     ?? $visit->school->phone;
        }

        $targetDateStr = $visit->target_date 
            ? Carbon::parse($visit->target_date)->format('d M Y') 
            : ($visit->target_month_year ?: 'Belum ditentukan');

        $methodLabel = $visit->socialization_method === 'Lainnya' 
            ? ($visit->other_activity_name ?: 'Sosialisasi Khusus') 
            : ($visit->socialization_method ?: 'Presentasi Sosialisasi');

        $totalEstimatedStudents = ($visit->grade_12_class_count && $visit->students_per_class)
            ? ($visit->grade_12_class_count * $visit->students_per_class)
            : null;

        $desc = "Follow-up konfirmasi FIX jadwal sosialisasi kampus UBSI. Target hasil kunjungan: {$targetDateStr} (Waktu: {$visit->target_time}) - Metode: {$methodLabel} ({$visit->grade_12_class_count} kelas 12). PIC: {$visit->recipient_name} ({$visit->recipient_position}).";

        $followupDate = $visit->next_followup_date;
        if (!$followupDate) {
            if ($visit->target_date) {
                // Set follow-up date 5 days before target or 2 days from now
                $targetCarbon = Carbon::parse($visit->target_date);
                $followupDate = $targetCarbon->isPast() ? now()->addDays(1) : $targetCarbon->subDays(5);
            } else {
                $followupDate = now()->addDays(2);
            }
        }

        $data = [
            'school_id' => $visit->school_id,
            'visit_id' => $visit->id,
            'user_id' => $visit->user_id ?: (auth()->id() ?: 1),
            'followup_date' => $followupDate,
            'method' => 'WhatsApp',
            'pic_name' => $visit->recipient_name,
            'pic_position' => $visit->recipient_position,
            'pic_phone' => $picPhone,
            'socialization_location' => $methodLabel,
            'target_audience_count' => $totalEstimatedStudents,
            'fixed_date' => $visit->target_date,
            'fixed_time' => $visit->target_time,
            'description' => $desc,
            'notes' => $desc,
        ];

        if ($followUp) {
            // Only update non-final statuses
            if ($followUp->status === 'pending' || $followUp->status === 'progress') {
                $followUp->update($data);
            }
            return $followUp;
        }

        $data['status'] = 'pending';
        $data['is_schedule_fixed'] = false;

        return FollowUp::create($data);
    }

    /**
     * Synchronize all field visits that haven't been linked to follow ups.
     */
    public function syncAllVisits(): int
    {
        $existingOrDismissedVisitIds = FollowUp::withTrashed()->whereNotNull('visit_id')->pluck('visit_id')->filter();

        $visits = SchoolVisit::with(['school.primaryContact', 'school.contacts'])
            ->whereNotIn('id', $existingOrDismissedVisitIds)
            ->get();

        $count = 0;
        foreach ($visits as $visit) {
            $this->syncVisit($visit);
            $count++;
        }

        return $count;
    }
}
