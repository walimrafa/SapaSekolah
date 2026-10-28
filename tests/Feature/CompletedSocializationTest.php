<?php

namespace Tests\Feature;

use App\Models\FollowUp;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CompletedSocializationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_fixed_socialization_can_be_confirmed_and_moves_to_completed_list(): void
    {
        $marketingRole = Role::firstOrCreate(['name' => 'Marketing'], ['description' => 'Marketing']);
        $user = User::factory()->create(['role_id' => $marketingRole->id]);
        $school = School::create([
            'npsn' => 'TEST' . strtoupper(bin2hex(random_bytes(4))),
            'name' => 'Sekolah Sosialisasi Test',
        ]);
        $followUp = FollowUp::create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'followup_date' => today(),
            'status' => FollowUp::STATUS_COMPLETED,
            'is_schedule_fixed' => true,
            'fixed_date' => today()->subDay(),
            'socialization_location' => 'Presentasi di kelas',
            'target_audience_count' => 120,
        ]);

        $this->actingAs($user)
            ->get(route('follow-ups.index'))
            ->assertOk()
            ->assertSee('Sekolah Sosialisasi Test')
            ->assertSee('Konfirmasi Sosialisasi Selesai');

        $this->actingAs($user)
            ->put(route('socializations.completed.confirm', $followUp), [
                'socialization_completed_at' => today()->subDay()->toDateString(),
                'socialization_completion_notes' => 'Presentasi kelas XII terlaksana.',
            ])
            ->assertRedirect(route('socializations.completed.index'));

        $this->assertDatabaseHas('follow_ups', [
            'id' => $followUp->id,
            'socialization_completion_notes' => 'Presentasi kelas XII terlaksana.',
        ]);

        $this->get(route('follow-ups.index'))
            ->assertOk()
            ->assertViewHas('allFollowups', fn ($followUps) => !$followUps->contains('id', $followUp->id));

        $this->get(route('socializations.completed.index'))
            ->assertOk()
            ->assertSee('Sekolah Sosialisasi Test')
            ->assertSee('Presentasi kelas XII terlaksana.');

        $this->delete(route('socializations.completed.reopen', $followUp))
            ->assertRedirect(route('socializations.completed.index'));

        $this->assertDatabaseHas('follow_ups', [
            'id' => $followUp->id,
            'socialization_completed_at' => null,
            'socialization_completion_notes' => null,
        ]);
    }

    public function test_unfixed_schedule_cannot_be_confirmed_as_completed(): void
    {
        $marketingRole = Role::firstOrCreate(['name' => 'Marketing'], ['description' => 'Marketing']);
        $user = User::factory()->create(['role_id' => $marketingRole->id]);
        $school = School::create([
            'npsn' => 'TEST' . strtoupper(bin2hex(random_bytes(4))),
            'name' => 'Sekolah Jadwal Belum FIX',
        ]);
        $followUp = FollowUp::create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'followup_date' => today(),
            'status' => FollowUp::STATUS_PENDING,
            'is_schedule_fixed' => false,
        ]);

        $this->actingAs($user)
            ->put(route('socializations.completed.confirm', $followUp), [
                'socialization_completed_at' => today()->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('follow_ups', [
            'id' => $followUp->id,
            'socialization_completed_at' => today()->toDateString(),
        ]);
    }
}
