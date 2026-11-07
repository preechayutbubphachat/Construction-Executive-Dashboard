<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthCheckEndpointsTest extends TestCase
{
    public function test_liveness_endpoint_returns_ok_without_authentication(): void
    {
        $response = $this->getJson('/health/liveness');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'service' => 'ced-app',
            ])
            ->assertJsonStructure([
                'status',
                'service',
                'timestamp',
            ]);
    }

    public function test_readiness_endpoint_returns_ready_when_database_and_storage_are_healthy(): void
    {
        $response = $this->getJson('/health/readiness');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ready',
                'components' => [
                    'database' => [
                        'status' => 'ok',
                    ],
                    'storage' => [
                        'status' => 'ok',
                    ],
                ],
            ])
            ->assertJsonStructure([
                'status',
                'timestamp',
                'components' => [
                    'database' => ['status'],
                    'storage' => ['status', 'path'],
                ],
            ]);
    }

    public function test_readiness_endpoint_returns_503_when_database_fails(): void
    {
        DB::shouldReceive('connection->getPdo')
            ->once()
            ->andThrow(new \PDOException('Connection refused by database'));

        $response = $this->getJson('/health/readiness');

        $response->assertStatus(503)
            ->assertJson([
                'status' => 'unhealthy',
                'components' => [
                    'database' => [
                        'status' => 'failed',
                    ],
                ],
            ]);
    }
}
