<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Requestor API Feature Tests
 * 
 * Tests for requestor API endpoints
 */
class RequestorApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $requestorUser;
    protected $auth;

    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create a test requestor user
        $userId = Str::uuid()->toString();
        $this->requestorUser = [
            'user_id' => $userId,
            'name' => 'Test Requestor',
            'email' => 'requestor@test.com',
            'role' => 'requestor',
            'position' => 'Staff',
            'department' => 'IT'
        ];

        // Set up session auth
        $this->auth = $this->requestorUser;
    }

    /**
     * Test health check endpoint
     *
     * @return void
     */
    public function test_health_check_returns_healthy_status()
    {
        $response = $this->get('/api/health');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Service is healthy',
                 ])
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'status',
                         'timestamp',
                         'uptime'
                     ],
                     'meta' => [
                         'timestamp',
                         'version'
                     ]
                 ]);
    }

    /**
     * Test detailed health check endpoint
     *
     * @return void
     */
    public function test_detailed_health_check_returns_system_status()
    {
        $response = $this->get('/api/health/detailed');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'status',
                     'checks' => [
                         'database',
                         'cache',
                         'storage',
                         'memory'
                     ],
                     'timestamp',
                     'uptime',
                     'version'
                 ]);
    }

    /**
     * Test metrics endpoint requires authentication
     *
     * @return void
     */
    public function test_metrics_endpoint_requires_authentication()
    {
        $response = $this->get('/api/requestor/metrics');

        $response->assertStatus(401)
                 ->assertJson([
                     'success' => false,
                     'error' => [
                         'code' => 'UNAUTHENTICATED'
                     ]
                 ]);
    }

    /**
     * Test get metrics endpoint with authentication
     *
     * @return void
     */
    public function test_can_get_metrics_when_authenticated()
    {
        $this->withSession(['auth_user' => $this->auth]);

        $response = $this->get('/api/requestor/metrics');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Metrics retrieved successfully'
                 ])
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'total_pos',
                         'verified_pos',
                         'approved_pos',
                         'pending_pos',
                         'total_value',
                         'average_value'
                     ],
                     'meta'
                 ]);
    }

    /**
     * Test get recent purchase orders
     *
     * @return void
     */
    public function test_can_get_recent_purchase_orders()
    {
        $this->withSession(['auth_user' => $this->auth]);

        $response = $this->get('/api/requestor/purchase-orders/recent');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Recent purchase orders retrieved successfully'
                 ])
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data',
                     'meta'
                 ]);
    }

    /**
     * Test recent purchase orders with invalid limit
     *
     * @return void
     */
    public function test_recent_purchase_orders_validates_limit()
    {
        $this->withSession(['auth_user' => $this->auth]);

        $response = $this->get('/api/requestor/purchase-orders/recent?limit=100');

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => [
                         'code' => 'VALIDATION_ERROR'
                     ]
                 ]);
    }

    /**
     * Test get paginated purchase orders
     *
     * @return void
     */
    public function test_can_get_paginated_purchase_orders()
    {
        $this->withSession(['auth_user' => $this->auth]);

        $response = $this->get('/api/requestor/purchase-orders?per_page=10&page=1');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Purchase orders retrieved successfully'
                 ])
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'items',
                         'pagination' => [
                             'total',
                             'per_page',
                             'current_page',
                             'last_page',
                             'from',
                             'to'
                         ]
                     ],
                     'meta'
                 ]);
    }

    /**
     * Test get purchase orders with search filter
     *
     * @return void
     */
    public function test_can_search_purchase_orders()
    {
        $this->withSession(['auth_user' => $this->auth]);

        $response = $this->get('/api/requestor/purchase-orders?search=office');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'items',
                         'pagination'
                     ]
                 ]);
    }

    /**
     * Test get purchase orders with status filter
     *
     * @return void
     */
    public function test_can_filter_purchase_orders_by_status()
    {
        $this->withSession(['auth_user' => $this->auth]);

        $response = $this->get('/api/requestor/purchase-orders?status=Approved');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'items',
                         'pagination'
                     ]
                 ]);
    }

    /**
     * Test get statistics endpoint
     *
     * @return void
     */
    public function test_can_get_statistics()
    {
        $this->withSession(['auth_user' => $this->auth]);

        $response = $this->get('/api/requestor/statistics');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Statistics retrieved successfully'
                 ])
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'period',
                         'total_pos',
                         'total_value',
                         'by_status',
                         'by_month'
                     ],
                     'meta'
                 ]);
    }

    /**
     * Test statistics with date range
     *
     * @return void
     */
    public function test_can_get_statistics_with_date_range()
    {
        $this->withSession(['auth_user' => $this->auth]);

        $response = $this->get('/api/requestor/statistics?date_from=2025-10-01&date_to=2025-10-31');

        $response->assertStatus(200)
                 ->assertJsonPath('data.period.from', '2025-10-01')
                 ->assertJsonPath('data.period.to', '2025-10-31');
    }

    /**
     * Test statistics validates date format
     *
     * @return void
     */
    public function test_statistics_validates_date_format()
    {
        $this->withSession(['auth_user' => $this->auth]);

        $response = $this->get('/api/requestor/statistics?date_from=invalid-date');

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => [
                         'code' => 'VALIDATION_ERROR'
                     ]
                 ]);
    }

    /**
     * Test statistics validates date range
     *
     * @return void
     */
    public function test_statistics_validates_date_range()
    {
        $this->withSession(['auth_user' => $this->auth]);

        $response = $this->get('/api/requestor/statistics?date_from=2025-12-01&date_to=2025-11-01');

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => [
                         'code' => 'VALIDATION_ERROR'
                     ]
                 ]);
    }

    /**
     * Test response format consistency
     *
     * @return void
     */
    public function test_all_responses_follow_standard_format()
    {
        $this->withSession(['auth_user' => $this->auth]);

        $endpoints = [
            '/api/requestor/metrics',
            '/api/requestor/purchase-orders/recent',
            '/api/requestor/purchase-orders',
            '/api/requestor/statistics'
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->get($endpoint);

            $response->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta' => [
                    'timestamp',
                    'version'
                ]
            ]);
        }
    }

    /**
     * Test API version in meta
     *
     * @return void
     */
    public function test_api_returns_version_in_meta()
    {
        $this->withSession(['auth_user' => $this->auth]);

        $response = $this->get('/api/requestor/metrics');

        $response->assertJsonPath('meta.version', '1.0.0');
    }
}
