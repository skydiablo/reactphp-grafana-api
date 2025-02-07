<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\MockObject\MockObject;
use SkyDiablo\Grafana\API\GrafanaUser;
use SkyDiablo\Grafana\GrafanaClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class GrafanaUserTest extends TestCase
{
    private MockObject|GrafanaClient $client;
    private GrafanaUser $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(GrafanaClient::class);
        $this->service = new GrafanaUser($this->client);
    }

    public function testGetCurrentUserReturnsJson(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getHeaderLine')->willReturn('application/json');
        $response->method('getBody')->willReturn(new class {
            public function getContents() {
                return json_encode(['id' => 1, 'name' => 'Test User']);
            }
        });

        $this->client->method('request')->willReturn(\React\Promise\resolve($response));

        $this->service->getCurrentUser()->then(function ($result) {
            $this->assertIsArray($result);
            $this->assertEquals(1, $result['id']);
            $this->assertEquals('Test User', $result['name']);
        });
    }

    public function testGetUserByIdReturnsJson(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getHeaderLine')->willReturn('application/json');
        $response->method('getBody')->willReturn(new class {
            public function getContents() {
                return json_encode(['id' => 2, 'name' => 'Another User']);
            }
        });

        $this->client->method('request')->willReturn(\React\Promise\resolve($response));

        $this->service->getUserById(2)->then(function ($result) {
            $this->assertIsArray($result);
            $this->assertEquals(2, $result['id']);
            $this->assertEquals('Another User', $result['name']);
        });
    }

    public function testUpdateUserReturnsJson(): void 
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getHeaderLine')->willReturn('application/json');
        $response->method('getBody')->willReturn(new class {
            public function getContents() {
                return json_encode(['message' => 'User updated']);
            }
        });

        $this->client->method('request')->willReturn(\React\Promise\resolve($response));

        $userData = ['name' => 'Updated Name'];
        $this->service->updateUser(1, $userData)->then(function ($result) {
            $this->assertIsArray($result);
            $this->assertEquals('User updated', $result['message']);
        });
    }

    public function testDeleteUserReturnsJson(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getHeaderLine')->willReturn('application/json');
        $response->method('getBody')->willReturn(new class {
            public function getContents() {
                return json_encode(['message' => 'User deleted']);
            }
        });

        $this->client->method('request')->willReturn(\React\Promise\resolve($response));

        $this->service->deleteUser(1)->then(function ($result) {
            $this->assertIsArray($result);
            $this->assertEquals('User deleted', $result['message']);
        });
    }
} 