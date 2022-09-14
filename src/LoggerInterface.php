<?php

declare(strict_types=1);

namespace Qase\PhpClientUtils;

interface LoggerInterface
{
    public function write(string $message, string $prefix = '[Qase reporter]'): void;

    public function writeln(string $message, string $prefix = '[Qase reporter]'): void;
}
