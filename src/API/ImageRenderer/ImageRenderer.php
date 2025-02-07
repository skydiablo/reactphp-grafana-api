<?php

declare(strict_types=1);

namespace SkyDiablo\Grafana\API\ImageRenderer;

use React\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use SkyDiablo\Grafana\Exceptions\FileSaveException;
use SkyDiablo\Grafana\GrafanaClient;

class ImageRenderer
{
    private GrafanaClient $client;

    public function __construct(
        GrafanaClient $client
    ) {
        $this->client = $client;
    }

    /**
     * Exportiert ein Grafana-Dashboard-Panel als Bild
     * 
     * @param PanelOptions $options Die Export-Optionen
     * @return PromiseInterface<string> Promise mit dem Bildinhalt
     */
    public function panel(
        PanelOptions $options
    ): PromiseInterface {
        $path = sprintf(
            'render/d-solo/%s%s',
            urlencode($options->dashboardUid),
            $options->database ? '/db/' . urlencode($options->database) : ''
        );

        return $this->client->request($path, $options->toQueryParams())
            ->then(function (ResponseInterface $response) {
                return (string) $response->getBody();
            });
    }

    /**
     * Speichert ein exportiertes Panel in einer Datei
     * 
     * @param string $outputPath Pfad zur Ausgabedatei
     * @param PanelOptions $options Die Export-Optionen
     * @return PromiseInterface<string> Promise mit dem Pfad zur Ausgabedatei
     */
    public function panelToFile(
        string $outputPath,
        PanelOptions $options
    ): PromiseInterface {
        return $this->panel($options)
            ->then(function (string $imageContent) use ($outputPath) {
                try {
                    $result = file_put_contents($outputPath, $imageContent);
                } catch (\Throwable $e) {
                    throw new FileSaveException(
                        'Failed to open stream: No such file or directory'
                    );
                }
                if ($result === false) {
                    throw new FileSaveException(
                        'Error saving file: ' . $outputPath
                    );
                }
                return $outputPath;
            });
    }
}
