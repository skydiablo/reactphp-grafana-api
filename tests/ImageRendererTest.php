<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SkyDiablo\Grafana\GrafanaClient;
use SkyDiablo\Grafana\Exceptions\ApiException;
use SkyDiablo\Grafana\Exceptions\FileSaveException;
use Psr\Http\Message\ResponseInterface;
use SkyDiablo\Grafana\API\ImageRenderer\ImageRenderer;
use SkyDiablo\Grafana\API\ImageRenderer\PanelOptions;

use function React\Async\await;
use function React\Promise\resolve;
use function React\Promise\reject;

class ImageRendererTest extends TestCase
{
    private MockObject $clientMock;
    private ImageRenderer $imageRenderer;
    private ?string $tempFilePath = null;

    protected function setUp(): void
    {
        $this->clientMock = $this->createMock(GrafanaClient::class);
        $this->imageRenderer = new ImageRenderer($this->clientMock);
    }

    protected function tearDown(): void
    {
        if ($this->tempFilePath && file_exists($this->tempFilePath)) {
            unlink($this->tempFilePath);
        }
    }

    public function testPanelReturnsImageContent(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(200);
        $responseMock->method('getBody')->willReturn('image-content');

        $this->clientMock->method('request')->willReturn(resolve($responseMock));

        $options = new PanelOptions('dashboardUid', 1);
        $promise = $this->imageRenderer->panel($options);
        $promise->then(function ($content) {
            $this->assertEquals('image-content', $content);
        });
    }

    public function testPanelThrowsApiExceptionOnError(): void
    {
        $this->clientMock->method('request')->willReturn(reject(new \Exception('Request failed')));

        $this->expectException(ApiException::class);

        $options = new PanelOptions('dashboardUid', 1);
        await(
            $this->imageRenderer
                ->panel($options)
                ->then(null, function ($e) {
                    throw new ApiException('Rendering failed', 0, $e);
                }),
        );
    }

    public function testPanelToFileThrowsFileSaveExceptionOnError(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(200);
        $responseMock->method('getBody')->willReturn('image-content');

        $this->clientMock->method('request')->willReturn(resolve($responseMock));

        $options = new PanelOptions('dashboardUid', 1);
        await(
            $this->imageRenderer
                ->panelToFile('/invalid/path', $options)
                ->then(null, function ($e) {
                    $this->assertInstanceOf(FileSaveException::class, $e);
                }),
        );
    }

    public function testPanelToFileSavesImageContent(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(200);
        $responseMock->method('getBody')->willReturn('image-content');

        $this->clientMock->method('request')->willReturn(resolve($responseMock));

        $options = new PanelOptions('dashboardUid', 1);
        $this->tempFilePath = tempnam(sys_get_temp_dir(), 'grafana_test_');

        await($this->imageRenderer->panelToFile($this->tempFilePath, $options)->then(function ($path) {
            $this->assertEquals($this->tempFilePath, $path);
            $this->assertFileExists($this->tempFilePath);
            $this->assertEquals('image-content', file_get_contents($this->tempFilePath));
        }));
    }
}