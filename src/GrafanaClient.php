<?php

declare(strict_types=1);

namespace SkyDiablo\Grafana;

use React\Http\Browser;
use React\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use SkyDiablo\Grafana\Exceptions\ApiException;

class GrafanaClient
{
    private Browser $browser;
    private string $grafanaUrl;
    private string $apiToken;

    public function __construct(
        string $grafanaUrl,
        string $apiToken,
        ?Browser $browser = null
    ) {
        $this->grafanaUrl = rtrim($grafanaUrl, '/');
        $this->apiToken = $apiToken;
        $this->browser = $browser ?? new Browser();
    }

    /**
     * Führt einen HTTP-Request gegen die Grafana-API aus
     * 
     * @param string $path Der API-Pfad
     * @param array $queryParams Query-Parameter
     * @param string $method HTTP-Methode
     * @return PromiseInterface<ResponseInterface>
     */
    public function request(
        string $path,
        array $queryParams = [],
        string $method = 'GET'
    ): PromiseInterface {
        if (empty($path)) {
            throw new \InvalidArgumentException('Der API-Pfad darf nicht leer sein');
        }
        $method = strtoupper($method);
        $url = $this->grafanaUrl . '/' . ltrim($path, '/');
        if (!empty($queryParams) && $method === 'GET') {
            $url .= '?' . http_build_query($queryParams);
        }

        $request = $this->browser->withHeader('Authorization', 'Bearer ' . $this->apiToken);

        switch ($method) {
            case 'POST':
            case 'PUT':
                return $request->request($method, $url, [], json_encode($queryParams));
            default:
                return $request->request($method, $url);
        }
    }
} 