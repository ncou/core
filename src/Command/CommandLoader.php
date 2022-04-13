<?php

declare(strict_types=1);

namespace Chiron\Core\Command;

use Chiron\Core\Command\AbstractCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

use Chiron\Injector\InjectorAwareTrait;
use Chiron\Injector\InjectorAwareInterface;

use Chiron\Container\SingletonInterface;

use Chiron\Core\Exception\ImproperlyConfiguredException;

use Chiron\Injector\Injector;

// TODO : utiliser l'attribut PHP8 "AsCommand" pour récupérer le nom de la commande. Ca permettra via reflection de préparer la liste des commandes.
//https://symfony.com/blog/new-in-symfony-5-3-lazy-command-description

//https://github.com/contributte/console/blob/master/src/CommandLoader/ContainerCommandLoader.php

/**
 * Loads commands from a PSR-11 container.
 */
// TODO : créer une méthode set($key, $value) pour ajouter une command à la liste $this->commandMap, et virer le parametre $commandMap du container, ou alors l'initialiser pas défaut à []
// TODO : passer la classe en final et virer les protected !!!!!
// TODO : il faudrait faire un implements SingletonInterface !!!!
// TODO : utiliser plutot cette classe qui fait déjà ce travail : https://github.com/symfony/console/blob/058553870f7809087fa80fa734704a21b9bcaeb2/CommandLoader/ContainerCommandLoader.php       Par contre il faudra surement utiliser un principe de mutation pour injecter automatiquement le container !!!!
// TODO : utiliser un containerawaretrait ?
// TODO : ajouter un implement SigletonInterface
class CommandLoader implements CommandLoaderInterface, SingletonInterface
{
    private Injector $injector;

    /** @var array<string, class-string> An array with command names as keys and service ids as values */
    private array $commandMap = [];

    public function __construct(Injector $injector)
    {
        $this->injector = $injector;
    }

    // TODO : renommer la méthode en register() + ajouter phpdoc
    public function set(string $command): void
    {
        if (! class_exists($command)) {
            throw new ImproperlyConfiguredException(sprintf('Command class "%s" does not exist.', $command));
        }

        if (! is_subclass_of($command, Command::class)) {
            // TODO : mettre plutot un message du type : Instance of XXX espected, received get_type_debug()
            throw new ImproperlyConfiguredException(sprintf('Command class "%s" does not extends from %s.', $command, Command::class));
        }

        $name = call_user_func([$command, 'getDefaultName']);

        if ($name === null) {
            throw new ImproperlyConfiguredException(sprintf('Command default name is not defined in the command "%s".', $command));
        }

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

        // TODO : Attention comme on utilise pas un container->get() on n'applique pas les mutations lorsqu'on récupére la commande :-( à voir si ce fonctionnement est voulu.
        return $this->injector->build($this->commandMap[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name): bool
    {
        return isset($this->commandMap[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getNames(): array
    {
        return array_keys($this->commandMap);
    }
}
