<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\AppSettingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LogoAutoSyncTest extends TestCase
{
    use DatabaseTransactions;

    private array $originalSettings;
    private ?string $originalLogoContent = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalSettings = AppSettingService::all();
        if (file_exists(public_path('logo-sapa-sekolah.png'))) {
            $this->originalLogoContent = file_get_contents(public_path('logo-sapa-sekolah.png'));
        }
    }

    protected function tearDown(): void
    {
        AppSettingService::setMany($this->originalSettings);
        if ($this->originalLogoContent !== null) {
            file_put_contents(public_path('logo-sapa-sekolah.png'), $this->originalLogoContent);
            AppSettingService::syncLogoToPublicAssets(public_path('logo-sapa-sekolah.png'));
        }
        parent::tearDown();
    }

    public function test_app_setting_get_logo_url_includes_cache_buster(): void
    {
        $logoUrl = AppSettingService::getLogoUrl();
        $this->assertNotEmpty($logoUrl);
        $this->assertStringContainsString('?v=', $logoUrl);

        $cleanUrl = AppSettingService::getLogoUrl(false);
        $this->assertStringNotContainsString('?v=', $cleanUrl);
    }

    public function test_sync_logo_to_public_assets_creates_and_updates_all_icons(): void
    {
        // Create a temporary 100x100 dummy PNG image
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_logo_') . '.png';
        $img = imagecreatetruecolor(100, 100);
        $color = imagecolorallocate($img, 11, 2, 62);
        imagefilledrectangle($img, 0, 0, 100, 100, $color);
        imagepng($img, $tmpFile);
        imagedestroy($img);

        $result = AppSettingService::syncLogoToPublicAssets($tmpFile);
        $this->assertTrue($result);

        $this->assertFileExists(public_path('favicon.png'));
        $this->assertFileExists(public_path('favicon.ico'));
        $this->assertFileExists(public_path('favicon-32x32.png'));
        $this->assertFileExists(public_path('favicon-16x16.png'));
        $this->assertFileExists(public_path('apple-touch-icon.png'));
        $this->assertFileExists(public_path('pwa-icon-192.png'));
        $this->assertFileExists(public_path('pwa-icon-512.png'));

        @unlink($tmpFile);
    }

    public function test_artisan_sync_logo_command_executes_successfully(): void
    {
        $this->artisan('app:sync-logo')
            ->expectsOutputToContain('Berhasil menyinkronkan logo')
            ->assertSuccessful();
    }

    public function test_uploading_logo_via_settings_updates_branding_and_syncs(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $file = UploadedFile::fake()->image('custom_school_logo.png', 200, 200);

        $payload = [
            'app_name' => 'SAPA Sekolah Test',
            'app_subtitle' => 'Command Center Test',
            'header_title' => 'Header Title Test',
            'footer_text' => 'Footer Text Test',
            'footer_subtext' => 'Footer Subtext Test',
            'campus_name' => 'UBSI Test',
            'campus_address' => 'Jl. Test No. 123',
            'campus_latitude' => -6.3054,
            'campus_longitude' => 107.2995,
            'logo_file' => $file,
        ];

        $response = $this->actingAs($admin)
            ->post(route('settings.update'), $payload);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('toast.type', 'success');

        $activeLogo = AppSettingService::get('app_logo_url');
        $this->assertStringStartsWith('/storage/branding/', $activeLogo);
        $this->assertFileExists(public_path(ltrim($activeLogo, '/')));
    }

    public function test_layouts_render_dynamic_logo_url_for_all_favicons(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $logoUrl = AppSettingService::getLogoUrl();

        // 1. Dashboard layout
        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('<link rel="icon" type="image/png" href="' . $logoUrl . '"', false);
        $response->assertSee('<link rel="icon" type="image/png" sizes="32x32" href="' . $logoUrl . '"', false);
        $response->assertSee('<link rel="icon" type="image/png" sizes="16x16" href="' . $logoUrl . '"', false);
        $response->assertSee('<link rel="shortcut icon" href="' . $logoUrl . '"', false);

        // 2. Guest Login layout (logout admin first)
        auth()->logout();
        $guestResponse = $this->get(route('login'));
        $guestResponse->assertOk();
        $guestResponse->assertSee('<link rel="icon" type="image/png" href="' . $logoUrl . '"', false);
        $guestResponse->assertSee('<link rel="icon" type="image/png" sizes="32x32" href="' . $logoUrl . '"', false);
        $guestResponse->assertSee('<link rel="icon" type="image/png" sizes="16x16" href="' . $logoUrl . '"', false);
    }
}
