<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\QuestionnaireQuestion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MasterQuestionnaireTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $pimpinan;
    protected User $koordinator;
    protected User $marketing;
    protected User $cs;
    protected Campus $campus;

    protected function setUp(): void
    {
        parent::setUp();

        $uid = uniqid();
        $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Administrator']);
        $pimpinanRole = Role::firstOrCreate(['name' => 'Pimpinan'], ['description' => 'Pimpinan']);
        $koordinatorRole = Role::firstOrCreate(['name' => 'Koordinator Marketing'], ['description' => 'Koordinator Marketing']);
        $marketingRole = Role::firstOrCreate(['name' => 'Marketing'], ['description' => 'Marketing Lapangan']);
        $csRole = Role::firstOrCreate(['name' => 'Customer Service'], ['description' => 'Customer Service']);

        $this->campus = Campus::firstOrCreate(['code' => 'KRW'], ['name' => 'UBSI Karawang', 'is_active' => true, 'is_default' => true]);

        $this->admin = User::factory()->create(['email' => "admin_{$uid}@example.com", 'role_id' => $adminRole->id, 'campus_id' => $this->campus->id]);
        $this->pimpinan = User::factory()->create(['email' => "pimpinan_{$uid}@example.com", 'role_id' => $pimpinanRole->id, 'campus_id' => $this->campus->id]);
        $this->koordinator = User::factory()->create(['email' => "koordinator_{$uid}@example.com", 'role_id' => $koordinatorRole->id, 'campus_id' => $this->campus->id]);
        $this->marketing = User::factory()->create(['email' => "marketing_{$uid}@example.com", 'role_id' => $marketingRole->id, 'campus_id' => $this->campus->id]);
        $this->cs = User::factory()->create(['email' => "cs_{$uid}@example.com", 'role_id' => $csRole->id, 'campus_id' => $this->campus->id]);
    }

    public function test_access_permissions_for_master_questionnaire(): void
    {
        // Admin, Pimpinan, dan Koordinator memiliki hak akses
        $this->actingAs($this->admin)->get(route('master-questionnaires.index'))->assertOk();
        $this->actingAs($this->pimpinan)->get(route('master-questionnaires.index'))->assertOk();
        $this->actingAs($this->koordinator)->get(route('master-questionnaires.index'))->assertOk();

        // Marketing dan CS ditolak (403 Forbidden)
        $this->actingAs($this->marketing)->get(route('master-questionnaires.index'))->assertForbidden();
        $this->actingAs($this->cs)->get(route('master-questionnaires.index'))->assertForbidden();

        // Guest diarahkan ke login
        auth()->logout();
        $this->get(route('master-questionnaires.index'))->assertRedirect(route('login'));
    }

    public function test_store_new_questionnaire_question(): void
    {
        $response = $this->actingAs($this->admin)->post(route('master-questionnaires.store'), [
            'section_title' => 'F. Minat Program Studi',
            'question_key' => 'prodi_teknologi_informasi',
            'question_text' => 'Saya sangat tertarik dengan program studi Sistem Informasi dan Teknologi Komputer.',
            'order_index' => 99,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('master-questionnaires.index'));

        $this->assertDatabaseHas('questionnaire_questions', [
            'section_title' => 'F. Minat Program Studi',
            'question_key' => 'prodi_teknologi_informasi',
            'order_index' => 99,
            'is_active' => 1,
        ]);

        $activeSections = QuestionnaireQuestion::getActiveSections();
        $this->assertArrayHasKey('F. Minat Program Studi', $activeSections);
        $this->assertArrayHasKey('prodi_teknologi_informasi', $activeSections['F. Minat Program Studi']);
    }

    public function test_update_questionnaire_question(): void
    {
        $question = QuestionnaireQuestion::create([
            'section_title' => 'A. Motivasi',
            'question_key' => 'test_motivasi_' . uniqid(),
            'question_text' => 'Teks awal sebelum update',
            'order_index' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('master-questionnaires.update', $question), [
            'section_title' => 'A. Motivasi Diperbarui',
            'question_key' => $question->question_key,
            'question_text' => 'Teks pernyataan telah diperbarui dengan sukses',
            'order_index' => 10,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('master-questionnaires.index'));

        $question->refresh();
        $this->assertEquals('A. Motivasi Diperbarui', $question->section_title);
        $this->assertEquals('Teks pernyataan telah diperbarui dengan sukses', $question->question_text);
        $this->assertEquals(10, $question->order_index);
    }

    public function test_destroy_questionnaire_question(): void
    {
        $question = QuestionnaireQuestion::create([
            'section_title' => 'A. Motivasi',
            'question_key' => 'to_delete_' . uniqid(),
            'question_text' => 'Pertanyaan untuk dihapus',
            'order_index' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('master-questionnaires.destroy', $question));
        $response->assertRedirect(route('master-questionnaires.index'));

        $this->assertDatabaseMissing('questionnaire_questions', [
            'id' => $question->id,
        ]);
    }

    public function test_reset_defaults_restores_16_standard_questions(): void
    {
        $response = $this->actingAs($this->admin)->post(route('master-questionnaires.reset-defaults'));
        $response->assertRedirect(route('master-questionnaires.index'));

        // Template standar memiliki 16 butir pertanyaan
        $this->assertEquals(16, QuestionnaireQuestion::count());
    }
}

