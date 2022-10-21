<?php

declare(strict_types=1);

namespace Qase\PhpClientUtils;

class RunResult
{
    private Config $config;
    private array $results = [];

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function addResult(array $result)
    {
        $this->results[] = $result;
    }
}
