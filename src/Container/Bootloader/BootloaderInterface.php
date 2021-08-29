<?php

declare(strict_types=1);

namespace Chiron\Core\Container\Bootloader;

use Chiron\Injector\InvokerInterface;

interface BootloaderInterface
{
    public function bootload(InvokerInterface $invoker): void;
}
