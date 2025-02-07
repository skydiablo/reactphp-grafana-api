<?php

declare(strict_types=1);

namespace SkyDiablo\Grafana\API;

use React\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use SkyDiablo\Grafana\GrafanaClient;

class GrafanaUser
{
    private GrafanaClient $client;

    public function __construct(GrafanaClient $client)
    {
        $this->client = $client;
    }

    private function requestWithJsonHandling(string $uri, array $data = [], string $method = 'GET'): PromiseInterface
    {
        return $this->client->request($uri, $data, $method)
            ->then(function (ResponseInterface $response) {
                $contentType = $response->getHeaderLine('Content-Type');
                if (strpos($contentType, 'application/json') === false) {
                    throw new \RuntimeException('Expected JSON response, got: ' . $contentType);
                }
                return json_decode($response->getBody()->getContents(), true);
            });
    }

    /**
     * Holt die Informationen des aktuellen Benutzers
     * 
     * @return PromiseInterface<ResponseInterface>
     */
    public function getCurrentUser(): PromiseInterface
    {
        return $this->requestWithJsonHandling('/api/user');
    }

    /**
     * Holt die Informationen eines Benutzers anhand der Benutzer-ID
     * 
     * @param int $userId Die ID des Benutzers
     * @return PromiseInterface<ResponseInterface>
     */
    public function getUserById(int $userId): PromiseInterface
    {
        return $this->requestWithJsonHandling('/api/users/' . $userId);
    }

    /**
     * Aktualisiert die Informationen eines Benutzers
     * 
     * @param int $userId Die ID des Benutzers
     * @param array $data Die zu aktualisierenden Daten
     * @return PromiseInterface<ResponseInterface>
     */
    public function updateUser(int $userId, array $data): PromiseInterface
    {
        return $this->requestWithJsonHandling('/api/users/' . $userId, $data, 'PUT');
    }

    /**
     * Löscht einen Benutzer
     * 
     * @param int $userId Die ID des Benutzers
     * @return PromiseInterface<ResponseInterface>
     */
    public function deleteUser(int $userId): PromiseInterface
    {
        return $this->requestWithJsonHandling('/api/admin/users/' . $userId, [], 'DELETE');
    }
} 