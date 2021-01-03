<?php

declare(strict_types=1);

namespace Chiron\Core\Bootloader;

use Chiron\Application;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Config\AppConfig;
use Chiron\Core\Console\ConsoleDispatcher;

// TODO : il faudrait déplacer cette classe dans le package chiron/chiron car on fait référence à la classe Application qui n'existe pas dans ce package !!!!
final class ConsoleDispatcherBootloader extends AbstractBootloader
{
    public function boot(Application $application, AppConfig $config): void
    {
        $application->addDispatcher(resolve(ConsoleDispatcher::class));
    }
}
