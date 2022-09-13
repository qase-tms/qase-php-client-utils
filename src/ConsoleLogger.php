<?php

declare(strict_types=1);

namespace Qase\PhpClientUtils;

class ConsoleLogger
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function write(string $message, string $prefix = '[Qase reporter]'): void
    {
        if (!$this->config->isLoggingEnabled()) {
            return;
        }

        if ($prefix) {
            $message = sprintf('%s %s', $prefix, $message);
        }

        print $message;
    }

    public function writeln(string $message, string $prefix = '[Qase reporter]'): void
    {
        if (!$this->config->isLoggingEnabled()) {
            return;
        }

        $this->write($message, $prefix);
        print PHP_EOL;
    }
}
