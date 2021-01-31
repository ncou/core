<?php

declare(strict_types=1);

namespace Chiron\Core\Container;

use Chiron\Container\Container;
use Chiron\Core\Container\Bootloader\BootloaderInterface;
use Chiron\Core\Container\Provider\ServiceProviderInterface;

/**
 * Manages Services living inside the Container.
 */
// TODO : renommer en ServicesManager au pluriel ???
final class ServiceManager
{
    /** @var Container */
    private $container;

    /** @var BootloaderInterface[] */
    private $bootloaders = [];

    /**
     * Indicates if the botloaders stack has been "booted".
     *
     * @var bool
     */
    private $booted = false;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Register a service provider.
     *
     * @param ServiceProviderInterface $provider
     */
    public function addProvider(ServiceProviderInterface $provider): void
    {
        $provider->register($this->container);
    }

    /**
     * Register a service provider.
     *
     * @param ServiceProviderInterface $provider
     */
    public function addBootloader(BootloaderInterface $bootloader): void
    {
        if ($this->booted) {
            $bootloader->bootload($this->container); // TODO : attention il faudrait pas gérer le cas ou il y a un doublon dans les bootloaders ajoutés ????
        } else {
            $this->bootloaders[] = $bootloader;
        }
    }

    /**
     * Boot the services bootloaders.
     */
    public function boot(): void
    {
        if (! $this->booted) {
            $this->booted = true;

            foreach ($this->bootloaders as $bootloader) {
                $bootloader->bootload($this->container);
            }
        }
    }
}
