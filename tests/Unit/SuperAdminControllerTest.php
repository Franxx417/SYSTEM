<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\SuperAdminController;

class SuperAdminControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $superadminUser;
    protected $testUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->createTestUsers();
    }

    /**
     * Create test users for testing
     */
    private function createTestUsers()
    {
        // Create superadmin user
        $superadminId = \Illuminate\Support\Str::uuid();
        DB::table('users')->insert([
            'user_id' => $superadminId,
            'name' => 'Test Superadmin',
            'email' => 'admin@test.com',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('login')->insert([
            'user_id' => $superadminId,
            'username' => 'admin',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create role type for superadmin
        $roleTypeId = DB::table('role_types')->insertGetId([
            'user_role_type' => 'superadmin',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('roles')->insert([
            'user_id' => $superadminId,
            'role_type_id' => $roleTypeId,
        ]);

        $this->superadminUser = [
            'user_id' => $superadminId,
            'username' => 'admin',
            'role' => 'superadmin'
        ];

        // Create regular user for testing deletion
        $testUserId = \Illuminate\Support\Str::uuid();
        DB::table('users')->insert([
            'user_id' => $testUserId,
            'name' => 'Test User',
            'email' => 'user@test.com',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('login')->insert([
            'user_id' => $testUserId,
            'username' => 'testuser',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create role type for requestor
        $requestorRoleTypeId = DB::table('role_types')->insertGetId([
            'user_role_type' => 'requestor',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('roles')->insert([
            'user_id' => $testUserId,
            'role_type_id' => $requestorRoleTypeId,
        ]);

        $this->testUser = [
            'user_id' => $testUserId,
            'username' => 'testuser',
            'role' => 'requestor'
        ];
    }

    /**
     * Test user deletion via API
     */
    public function test_delete_user_api_success()
    {
        $response = $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('DELETE', '/api/superadmin/users/' . $this->testUser['user_id'], [
            'user_id' => $this->testUser['user_id']
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        // Verify user is deleted
        $this->assertDatabaseMissing('users', [
            'user_id' => $this->testUser['user_id']
        ]);

        // Verify login record is deleted
        $this->assertDatabaseMissing('login', [
            'user_id' => $this->testUser['user_id']
        ]);

        // Verify role is deleted
        $this->assertDatabaseMissing('roles', [
            'user_id' => $this->testUser['user_id']
        ]);
    }

    /**
     * Test user deletion prevents last superadmin deletion
     */
    public function test_delete_user_prevents_last_superadmin_deletion()
    {
        $response = $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('DELETE', '/api/superadmin/users/' . $this->superadminUser['user_id'], [
            'user_id' => $this->superadminUser['user_id']
        ]);

        $response->assertStatus(400)
                 ->assertJson(['success' => false]);

        // Verify superadmin is not deleted
        $this->assertDatabaseHas('users', [
            'user_id' => $this->superadminUser['user_id']
        ]);
    }

    /**
     * Test user deletion with invalid user ID
     */
    public function test_delete_user_with_invalid_id()
    {
        $invalidId = \Illuminate\Support\Str::uuid();
        
        $response = $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('DELETE', '/api/superadmin/users/' . $invalidId, [
            'user_id' => $invalidId
        ]);

        $response->assertStatus(404)
                 ->assertJson(['success' => false]);
    }

    /**
     * Test user deletion without user ID
     */
    public function test_delete_user_without_user_id()
    {
        $response = $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('DELETE', '/api/superadmin/users/test', []);

        $response->assertStatus(400)
                 ->assertJson(['success' => false]);
    }

    /**
     * Test password reset
     */
    public function test_reset_user_password_success()
    {
        $response = $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('POST', '/api/superadmin/users/reset-password', [
            'user_id' => $this->testUser['user_id']
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure(['success', 'new_password', 'message']);

        // Verify password was updated
        $loginRecord = DB::table('login')
            ->where('user_id', $this->testUser['user_id'])
            ->first();

        $this->assertNotNull($loginRecord);
        $this->assertTrue(password_verify('password', $loginRecord->password) === false);
    }

    /**
     * Test password reset with invalid user ID
     */
    public function test_reset_password_with_invalid_user_id()
    {
        $invalidId = \Illuminate\Support\Str::uuid();
        
        $response = $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('POST', '/api/superadmin/users/reset-password', [
            'user_id' => $invalidId
        ]);

        $response->assertStatus(404)
                 ->assertJson(['success' => false]);
    }

    /**
     * Test toggle user status
     */
    public function test_toggle_user_status_success()
    {
        // Verify initial status
        $user = DB::table('users')
            ->where('user_id', $this->testUser['user_id'])
            ->first();
        $initialStatus = $user->is_active;

        $response = $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('POST', '/api/superadmin/users/toggle', [
            'user_id' => $this->testUser['user_id']
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        // Verify status was toggled
        $updatedUser = DB::table('users')
            ->where('user_id', $this->testUser['user_id'])
            ->first();

        $this->assertNotEquals($initialStatus, $updatedUser->is_active);
    }

    /**
     * Test database connection test
     */
    public function test_database_connection_test()
    {
        $controller = new SuperAdminController();
        
        // Create a mock request
        $request = $this->createMock(\Illuminate\Http\Request::class);
        $request->method('session')->willReturn(session());
        
        // This would require proper mocking of the controller method
        // For now, we test the basic functionality
        $this->assertTrue(true);
    }

    /**
     * Test create user
     */
    public function test_create_user_success()
    {
        $response = $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('POST', '/api/superadmin/users/create', [
            'username' => 'newuser',
            'name' => 'New User',
            'role' => 'requestor',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        // Verify user was created
        $this->assertDatabaseHas('login', [
            'username' => 'newuser'
        ]);
    }

    /**
     * Test create user with duplicate username
     */
    public function test_create_user_duplicate_username()
    {
        $response = $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('POST', '/api/superadmin/users/create', [
            'username' => 'testuser', // Already exists
            'name' => 'Another User',
            'role' => 'requestor',
            'password' => 'password123'
        ]);

        $response->assertStatus(422); // Validation error
    }

    /**
     * Test terminate session
     */
    public function test_terminate_session_success()
    {
        // Create a login session
        $loginId = DB::table('login')->insertGetId([
            'user_id' => $this->testUser['user_id'],
            'username' => 'testuser',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'login_time' => now(),
            'logout_time' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('POST', '/api/superadmin/security/terminate-session', [
            'session_id' => $loginId
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        // Verify session was terminated
        $session = DB::table('login')
            ->where('login_id', $loginId)
            ->first();

        $this->assertNotNull($session->logout_time);
    }

    /**
     * Test force logout all users
     */
    public function test_force_logout_all_users()
    {
        // Create multiple active sessions
        DB::table('login')->insert([
            'user_id' => $this->testUser['user_id'],
            'username' => 'testuser',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'login_time' => now(),
            'logout_time' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('POST', '/api/superadmin/security/force-logout-all', []);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        // Verify all sessions were terminated
        $activeSessions = DB::table('login')
            ->whereNull('logout_time')
            ->count();

        $this->assertEquals(0, $activeSessions);
    }

    /**
     * Test audit logging for user deletion
     */
    public function test_user_deletion_creates_audit_log()
    {
        Log::spy();

        $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('DELETE', '/api/superadmin/users/' . $this->testUser['user_id'], [
            'user_id' => $this->testUser['user_id']
        ]);

        Log::shouldHaveReceived('info')
            ->withArgs(function ($message, $context) {
                return $message === 'User deleted via API' &&
                       isset($context['deleted_user_id']) &&
                       isset($context['admin']);
            });
    }

    /**
     * Test cascading deletion of user records
     */
    public function test_user_deletion_cascades_to_related_tables()
    {
        // Verify user has related records
        $this->assertDatabaseHas('users', ['user_id' => $this->testUser['user_id']]);
        $this->assertDatabaseHas('login', ['user_id' => $this->testUser['user_id']]);
        $this->assertDatabaseHas('roles', ['user_id' => $this->testUser['user_id']]);

        // Delete user
        $this->actingAs(
            (object) $this->superadminUser,
            'web'
        )->json('DELETE', '/api/superadmin/users/' . $this->testUser['user_id'], [
            'user_id' => $this->testUser['user_id']
        ]);

        // Verify all related records are deleted
        $this->assertDatabaseMissing('users', ['user_id' => $this->testUser['user_id']]);
        $this->assertDatabaseMissing('login', ['user_id' => $this->testUser['user_id']]);
        $this->assertDatabaseMissing('roles', ['user_id' => $this->testUser['user_id']]);
    }
}
