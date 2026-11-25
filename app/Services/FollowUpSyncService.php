<?php

namespace App\Services;

use App\Models\AcademicPeriod;
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

        $picPhone = $visit->recipient_phone;
        if (!$picPhone && $visit->school) {
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

        $agendaCategory = $visit->agenda_category ?: SchoolVisit::AGENDA_PENJADWALAN_SOSIALISASI;
        $cats = $visit->agenda_categories;

        $parts = [];
        if (in_array(SchoolVisit::AGENDA_PENJADWALAN_SOSIALISASI, $cats)) {
            $parts[] = "Follow-up konfirmasi jadwal sosialisasi kampus UBSI (Target: {$targetDateStr}, Waktu: {$visit->target_time}, Metode: {$methodLabel})";
        }
        if (in_array(SchoolVisit::AGENDA_MENGANTAR_UNDANGAN, $cats)) {
            $attendanceLabel = $visit->invitation_attendance_label ?: 'Belum Konfirmasi';
            $eventName = $visit->event_name ? " [Event: {$visit->event_name}]" : '';
            $parts[] = "Follow-up respon undangan acara{$eventName} [Status: {$attendanceLabel}]";
        }
        if (in_array(SchoolVisit::AGENDA_KUNJUNGAN_LAINNYA, $cats)) {
            $parts[] = "Follow-up hasil audiensi dinas & silaturahmi";
        }

        $desc = (!empty($parts) ? implode(' | ', $parts) : "Follow-up kunjungan sekolah") . ". PIC: {$visit->recipient_name} ({$visit->recipient_position}).";
        if (!empty($visit->notes)) {
            $desc .= " Catatan: " . $visit->notes;
        }

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

        // Campus ID must strictly follow school's campus
        $campusId = $visit->school?->campus_id 
            ?: ($visit->campus_id ?: 1);
        $periodId = $visit->academic_period_id 
            ?: (AcademicPeriod::getCurrent()?->id ?: 1);

        $data = [
            'school_id' => $visit->school_id,
            'visit_id' => $visit->id,
            'agenda_category' => $agendaCategory,
            'event_name' => $visit->event_name,
            'invitation_attendance_status' => $visit->invitation_attendance_status,
            'campus_id' => $campusId,
            'academic_period_id' => $periodId,
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
            'google_drive_folder_url' => $visit->google_drive_folder_url,
            'google_drive_file_url' => $visit->google_drive_file_url,
        ];

        if ($followUp) {
            $data['agenda_category'] = $agendaCategory;
            if ($visit->invitation_attendance_status) {
                $data['invitation_attendance_status'] = $visit->invitation_attendance_status;
            }
            if ($visit->event_name) {
                $data['event_name'] = $visit->event_name;
            }
            // Update agenda target date and drive links
            if ($visit->target_date) {
                $data['fixed_date'] = $visit->target_date;
                $data['fixed_time'] = $visit->target_time;
            }
            if ($visit->google_drive_folder_url) {
                $data['google_drive_folder_url'] = $visit->google_drive_folder_url;
            }
            if ($visit->google_drive_file_url) {
                $data['google_drive_file_url'] = $visit->google_drive_file_url;
            }
            // Ensure campus_id is always synced with school's campus
            $data['campus_id'] = $campusId;
            if (empty($followUp->academic_period_id)) {
                $data['academic_period_id'] = $periodId;
            }

            if ($followUp->status === 'pending' || $followUp->status === 'progress') {
                $followUp->update($data);
            } else {
                $completedUpdates = array_filter([
                    'event_name' => $visit->event_name,
                    'invitation_attendance_status' => $visit->invitation_attendance_status,
                    'google_drive_folder_url' => $visit->google_drive_folder_url,
                    'google_drive_file_url' => $visit->google_drive_file_url,
                ]);
                if (!empty($completedUpdates)) {
                    $followUp->update($completedUpdates);
                }
            }
            return $followUp;
        }

        $data['status'] = 'pending';
        $data['is_schedule_fixed'] = !empty($visit->target_date);

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
