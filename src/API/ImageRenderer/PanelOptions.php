<?php

declare(strict_types=1);

namespace SkyDiablo\Grafana\API\ImageRenderer;

class PanelOptions
{
    public string $dashboardUid;
    public int $panelId;
    public int $width;
    public int $height;
    public string $from;
    public string $to;
    public string $format;
    public ?string $database;
    public array $variables;

    public function __construct(
        string $dashboardUid,
        int $panelId,
        int $width = 800,
        int $height = 600,
        string $from = 'now-6h',
        string $to = 'now',
        string $format = 'png',
        ?string $database = null,
        array $variables = []
    ) {
        $this->dashboardUid = $dashboardUid;
        $this->panelId = $panelId;
        $this->width = $width;
        $this->height = $height;
        $this->from = $from;
        $this->to = $to;
        $this->format = $format;
        $this->database = $database;
        $this->variables = $variables;
    }

    public function toQueryParams(): array
    {
        $queryParams = [
            'panelId' => $this->panelId,
            'width'   => $this->width,
            'height'  => $this->height,
            'from'    => $this->from,
            'to'      => $this->to,
            'format'  => $this->format,
        ];

        foreach ($this->variables as $key => $value) {
            $queryParams['var-'.$key] = $value;
        }

        return $queryParams;
    }

    public function setVariable(string $key, int|string $value): self
    {
        $clone = clone $this;
        $clone->variables[$key] = $value;

        return $clone;
    }


} 
