<?php

declare(strict_types=1);

namespace Chiron\Core\Command;

use Chiron\Core\Command\AbstractCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

https://github.com/contributte/console/blob/master/src/CommandLoader/ContainerCommandLoader.php

/**
 * Loads commands from a PSR-11 container.
 */
// TODO : créer une méthode set($key, $value) pour ajouter une command à la liste $this->commandMap, et virer le parametre $commandMap du container, ou alors l'initialiser pas défaut à []
// TODO : passer la classe en final et virer les protected !!!!!
// TODO : il faudrait faire un implements SingletonInterface !!!!
// TODO : utiliser plutot cette classe qui fait déjà ce travail : https://github.com/symfony/console/blob/058553870f7809087fa80fa734704a21b9bcaeb2/CommandLoader/ContainerCommandLoader.php       Par contre il faudra surement utiliser un principe de mutation pour injecter automatiquement le container !!!!
class CommandLoader implements CommandLoaderInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var array An array with command names as keys and service ids as values */
    private $commandMap = [];

    // TODO : lui passer plutot un Chiron\Container et non pas un PSR11 ContainerInterface !!!
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    // TODO : renommer en add()
    public function set(string $name, string $command): void
    {
        $this->commandMap[$name] = $command;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name): Command
    {
        if (! $this->has($name)) {
            throw new CommandNotFoundException(sprintf('Command "%s" does not exist.', $name));
        }

        $command = $this->container->get($this->commandMap[$name]);

        // TODO : Faire un test si la classe à un ContainerAwareInterface dans ce cas on injecte le container. Ou alors utiliser une mutation du container pour injecter le container automatiquement ?
        if ($command instanceof AbstractCommand) {
            $command->setContainer($this->container);
        }

        return $command;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name): bool
    {
        return isset($this->commandMap[$name]) && $this->container->has($this->commandMap[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getNames(): array
    {
        return array_keys($this->commandMap);
    }
}
