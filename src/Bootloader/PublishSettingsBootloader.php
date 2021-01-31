<?php

namespace Chiron\Core\Bootloader;

use Chiron\Core\Directories;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Core\Publisher;

final class PublishSettingsBootloader extends AbstractBootloader
{
    public function boot(Publisher $publisher, Directories $directories): void
    {
        $configPath = __DIR__ . '/../../config';

        // copy the configuration file template from the package "config" folder to the user "config" folder.
        $publisher->add($configPath . '/settings.php.dist', $directories->get('@config/settings.php'));
    }
}
