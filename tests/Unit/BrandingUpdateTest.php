<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BrandingUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected $superadminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestUser();
    }

    /**
     * Create a test superadmin user
     */
    private function createTestUser()
    {
        $userId = \Illuminate\Support\Str::uuid();
        DB::table('users')->insert([
            'user_id' => $userId,
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('login')->insert([
            'user_id' => $userId,
            'username' => 'admin',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $roleTypeId = DB::table('role_types')->insertGetId([
            'user_role_type' => 'superadmin',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('roles')->insert([
            'user_id' => $userId,
            'role_type_id' => $roleTypeId,
        ]);

        $this->superadminUser = [
            'user_id' => $userId,
            'username' => 'admin',
            'role' => 'superadmin'
        ];
    }

    /**
     * Test successful branding update with single field
     */
    public function test_branding_update_single_field_success()
    {
        $response = $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('POST', '/api/superadmin/branding/update', [
            'app_name' => 'Updated App Name'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure(['success', 'message', 'updated_fields', 'timestamp']);

        $this->assertDatabaseHas('settings', [
            'key' => 'app.name',
            'value' => 'Updated App Name'
        ]);
    }

    /**
     * Test successful branding update with multiple fields
     */
    public function test_branding_update_multiple_fields_success()
    {
        $response = $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('POST', '/api/superadmin/branding/update', [
            'app_name' => 'New App',
            'app_tagline' => 'New Tagline',
            'primary_color' => '#0d6efd',
            'font_family' => 'Roboto',
            'theme_mode' => 'dark'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('settings', ['key' => 'app.name', 'value' => 'New App']);
        $this->assertDatabaseHas('settings', ['key' => 'app.tagline', 'value' => 'New Tagline']);
        $this->assertDatabaseHas('settings', ['key' => 'branding.primary_color', 'value' => '#0d6efd']);
        $this->assertDatabaseHas('settings', ['key' => 'branding.font_family', 'value' => 'Roboto']);
        $this->assertDatabaseHas('settings', ['key' => 'branding.theme_mode', 'value' => 'dark']);
    }

    /**
     * Test color validation - valid hex codes
     */
    public function test_valid_color_hex_codes()
    {
        $colors = [
            '#0d6efd',
            '#FF0000',
            '#00ff00',
            '#0000FF',
            '#123abc'
        ];

        foreach ($colors as $color) {
            $response = $this->actingAs(
                (object) $this->superadminUser,
                'web'
            )->json('POST', '/api/superadmin/branding/update', [
                'primary_color' => $color
            ]);

            $response->assertStatus(200)
                     ->assertJson(['success' => true]);
        }
    }

    /**
     * Test color validation - invalid hex codes
     */
    public function test_invalid_color_hex_codes()
    {
        $invalidColors = [
            'invalid-color',
            '#GGGGGG',
            '#12345',
            'red',
            '#12345G'
        ];

        foreach ($invalidColors as $color) {
            $response = $this->actingAs(
                (object) $this->superadminUser,
                'web'
            )->json('POST', '/api/superadmin/branding/update', [
                'primary_color' => $color
            ]);

            $response->assertStatus(422)
                     ->assertJson(['success' => false]);
        }
    }

    /**
     * Test font size validation - valid range
     */
    public function test_valid_font_size()
    {
        $sizes = [12, 14, 16, 18];

        foreach ($sizes as $size) {
            $response = $this->actingAs(
                (object) $this->superadminUser,
                'web'
            )->json('POST', '/api/superadmin/branding/update', [
                'font_size' => $size
            ]);

            $response->assertStatus(200)
                     ->assertJson(['success' => true]);
        }
    }

    /**
     * Test font size validation - out of range
     */
    public function test_invalid_font_size()
    {
        $invalidSizes = [10, 11, 19, 20, 100];

        foreach ($invalidSizes as $size) {
            $response = $this->actingAs(
                (object) $this->superadminUser,
                'web'
            )->json('POST', '/api/superadmin/branding/update', [
                'font_size' => $size
            ]);

            $response->assertStatus(422);
        }
    }

    /**
     * Test logo position validation
     */
    public function test_logo_position_validation()
    {
        $validPositions = ['left', 'center', 'right'];

        foreach ($validPositions as $position) {
            $response = $this->actingAs(
                (object) $this->superadminUser,
                'web'
            )->json('POST', '/api/superadmin/branding/update', [
                'logo_position' => $position
            ]);

            $response->assertStatus(200);
        }

        // Test invalid position
        $response = $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('POST', '/api/superadmin/branding/update', [
            'logo_position' => 'invalid'
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test theme mode validation
     */
    public function test_theme_mode_validation()
    {
        $validModes = ['light', 'dark', 'auto'];

        foreach ($validModes as $mode) {
            $response = $this->actingAs(
                (object) $this->superadminUser,
                'web'
            )->json('POST', '/api/superadmin/branding/update', [
                'theme_mode' => $mode
            ]);

            $response->assertStatus(200);
        }
    }

    /**
     * Test button shadow validation
     */
    public function test_button_shadow_validation()
    {
        $validShadows = ['none', 'sm', 'md', 'lg'];

        foreach ($validShadows as $shadow) {
            $response = $this->actingAs(
                (object) $this->superadminUser,
                'web'
            )->json('POST', '/api/superadmin/branding/update', [
                'button_shadow' => $shadow
            ]);

            $response->assertStatus(200);
        }
    }

    /**
     * Test empty update (no data provided)
     */
    public function test_empty_update_fails()
    {
        $response = $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('POST', '/api/superadmin/branding/update', []);

        $response->assertStatus(400)
                 ->assertJson(['success' => false, 'error' => 'No branding data provided to update']);
    }

    /**
     * Test unauthorized access (no authentication)
     */
    public function test_unauthorized_access_no_auth()
    {
        $response = $this->json('POST', '/api/superadmin/branding/update', [
            'app_name' => 'Test'
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test field length validation
     */
    public function test_field_length_validation()
    {
        // App name too long
        $response = $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('POST', '/api/superadmin/branding/update', [
            'app_name' => str_repeat('a', 101)
        ]);

        $response->assertStatus(422);

        // App tagline too long
        $response = $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('POST', '/api/superadmin/branding/update', [
            'app_tagline' => str_repeat('a', 151)
        ]);

        $response->assertStatus(422);

        // Custom CSS too long
        $response = $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('POST', '/api/superadmin/branding/update', [
            'custom_css' => str_repeat('a', 5001)
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test button radius validation
     */
    public function test_button_radius_validation()
    {
        // Valid range
        for ($i = 0; $i <= 20; $i += 5) {
            $response = $this->actingAs(
                (object) $this->superadminUser,
                'web'
            )->json('POST', '/api/superadmin/branding/update', [
                'button_radius' => $i
            ]);

            $response->assertStatus(200);
        }

        // Out of range
        $response = $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('POST', '/api/superadmin/branding/update', [
            'button_radius' => 21
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test button padding validation
     */
    public function test_button_padding_validation()
    {
        // Valid range
        for ($i = 4; $i <= 16; $i += 4) {
            $response = $this->actingAs(
                (object) $this->superadminUser,
                'web'
            )->json('POST', '/api/superadmin/branding/update', [
                'button_padding' => $i
            ]);

            $response->assertStatus(200);
        }

        // Out of range
        $response = $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('POST', '/api/superadmin/branding/update', [
            'button_padding' => 17
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test sidebar position validation
     */
    public function test_sidebar_position_validation()
    {
        $validPositions = ['left', 'right', 'top'];

        foreach ($validPositions as $position) {
            $response = $this->actingAs(
                (object) $this->superadminUser,
                'web'
            )->json('POST', '/api/superadmin/branding/update', [
                'sidebar_position' => $position
            ]);

            $response->assertStatus(200);
        }
    }

    /**
     * Test response structure
     */
    public function test_response_structure()
    {
        $response = $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('POST', '/api/superadmin/branding/update', [
            'app_name' => 'Test App'
        ]);

        $response->assertJsonStructure([
            'success',
            'message',
            'updated_fields',
            'timestamp'
        ]);
    }

    /**
     * Test updated fields tracking
     */
    public function test_updated_fields_tracking()
    {
        $response = $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('POST', '/api/superadmin/branding/update', [
            'app_name' => 'Test',
            'primary_color' => '#ff0000'
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        
        $this->assertContains('app.name', $data['updated_fields']);
        $this->assertContains('branding.primary_color', $data['updated_fields']);
    }

    /**
     * Test all branding fields together
     */
    public function test_all_branding_fields()
    {
        $response = $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('POST', '/api/superadmin/branding/update', [
            'app_name' => 'Complete App',
            'app_tagline' => 'Complete Tagline',
            'app_description' => 'Complete Description',
            'logo_position' => 'center',
            'logo_size' => 75,
            'primary_color' => '#0d6efd',
            'secondary_color' => '#6c757d',
            'accent_color' => '#198754',
            'font_family' => 'Roboto',
            'font_size' => 16,
            'theme_mode' => 'dark',
            'sidebar_position' => 'right',
            'button_radius' => 8,
            'button_padding' => 10,
            'button_shadow' => 'md',
            'custom_css' => 'body { margin: 0; }'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        // Verify all settings were saved
        $this->assertDatabaseHas('settings', ['key' => 'app.name', 'value' => 'Complete App']);
        $this->assertDatabaseHas('settings', ['key' => 'branding.theme_mode', 'value' => 'dark']);
        $this->assertDatabaseHas('settings', ['key' => 'branding.custom_css', 'value' => 'body { margin: 0; }']);
    }
}
