<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PwaSystemTest extends TestCase
{
    use DatabaseTransactions;

    public function test_pwa_manifest_file_exists_and_is_valid(): void
    {
        $manifestPath = public_path('manifest.json');
        $this->assertFileExists($manifestPath);

        $content = file_get_contents($manifestPath);
        $this->assertJson($content);

        $json = json_decode($content, true);
        $this->assertEquals('SAPA Sekolah - Marketing Command Center', $json['name']);
        $this->assertEquals('SAPA Sekolah', $json['short_name']);
        $this->assertEquals('standalone', $json['display']);
        $this->assertEquals('/dashboard', $json['start_url']);
        $this->assertNotEmpty($json['icons']);

        // Check required icon sizes
        $sizes = array_column($json['icons'], 'sizes');
        $this->assertContains('192x192', $sizes);
        $this->assertContains('512x512', $sizes);

        // Verify physical icon files exist
        foreach ($json['icons'] as $icon) {
            $iconPath = public_path(ltrim($icon['src'], '/'));
            $this->assertFileExists($iconPath, "Icon file does not exist: {$icon['src']}");
        }
    }

    public function test_pwa_service_worker_and_offline_page_exist(): void
    {
        $swPath = public_path('sw.js');
        $this->assertFileExists($swPath);
        $swContent = file_get_contents($swPath);
        $this->assertStringContainsString('sapasekolah-pwa-', $swContent);
        $this->assertStringContainsString('offline.html', $swContent);
        $this->assertStringContainsString('addEventListener(\'install\'', $swContent);
        $this->assertStringContainsString('addEventListener(\'activate\'', $swContent);
        $this->assertStringContainsString('addEventListener(\'fetch\'', $swContent);

        $offlinePath = public_path('offline.html');
        $this->assertFileExists($offlinePath);
        $offlineContent = file_get_contents($offlinePath);
        $this->assertStringContainsString('Mode Offline - SAPA Sekolah', $offlineContent);
        $this->assertStringContainsString('Sedang Berada di Luar Jaringan', $offlineContent);
    }

    public function test_pwa_meta_tags_and_service_worker_rendered_in_views(): void
    {
        // 1. Check guest login screen
        $loginResponse = $this->get(route('login'));
        $loginResponse->assertOk();
        $loginResponse->assertSee('rel="manifest"', false);
        $loginResponse->assertSee('manifest.json');
        $loginResponse->assertSee('navigator.serviceWorker.register(\'/sw.js\')', false);

        // 2. Check authenticated dashboard
        $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('rel="manifest"', false);
        $response->assertSee('manifest.json');
        $response->assertSee('navigator.serviceWorker.register(\'/sw.js\')', false);
    }
}
