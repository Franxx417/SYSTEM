<?php

namespace Tests\Feature;

use Tests\TestCase;

class SuperadminBrandingApiTest extends TestCase
{
    

    

    public function test_validation_error_returns_422()
    {
        $response = $this->withSession(['auth_user' => ['role' => 'superadmin', 'username' => 'tester']])
            ->postJson('/api/superadmin/branding/update', [
                'primary_color' => '#ZZZZZZ'
            ]);

        $response->assertStatus(422);
        $response->assertJson(['error' => 'Validation failed']);
    }

    public function test_no_data_returns_400()
    {
        $response = $this->withSession(['auth_user' => ['role' => 'superadmin', 'username' => 'tester']])
            ->postJson('/api/superadmin/branding/update', []);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'No branding data provided to update']);
    }
}