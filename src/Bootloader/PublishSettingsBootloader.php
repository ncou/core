<?php

namespace Chiron\Core\Bootloader;

use Chiron\Core\Directories;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\PublishableCollection;

final class PublishSettingsBootloader extends AbstractBootloader
{
    public function boot(PublishableCollection $publishable, Directories $directories): void
    {
        $configPath = __DIR__ . '/../../config';

        // copy the configuration file template from the package "config" folder to the user "config" folder.
        $publishable->add($configPath . '/settings.php.dist', $directories->get('@config/settings.php'));
    }
}
