<?php

declare(strict_types=1);

use Chiron\Config\ConfigInterface;
use Chiron\Container\Container;
use Chiron\Container\FactoryInterface;
use Chiron\Core\Configure;
use Chiron\Core\Directories;
use Chiron\Core\Environment;
use Chiron\Core\Exception\ScopeException;
use Psr\Container\ContainerExceptionInterface;
use Chiron\Core\Config\SettingsConfig;

//https://github.com/laravel/framework/blob/43bea00fd27c76c01fd009e46725a54885f4d2a5/src/Illuminate/Foundation/helpers.php#L645

// TODO : ajouter les @throws ScopeException pour les différentes fonctions ci dessous !!!!!

// TODO : virer cette méthode !!!!
if (! function_exists('di')) {
    /**
     * Return the container instance.
     *
     * @return Container
     */
    function di(): Container
    {
         return Container::$instance;
    }
}

if (! function_exists('container')) {
    /**
     * Resolve given alias in the container.
     *
     * @param string $alias Class name or alias.
     *
     * @throws RuntimeException
     *
     * @return mixed
     */
    // TODO : permettre de ne rien passer en paramétre de la méthode container() et dans ce cas elle retournera l'instance du container, cela permet de faire des appels chainés : ex : container()->has('xxx')
    function container(string $alias, bool $forceNew = false)
    {
        //return (Container::$instance)->get($alias);

        $container = Container::$instance;

        if ($container === null) {
            throw new ScopeException('Container instance was not set.');
        }

        try {
            return $container->get($alias, $forceNew);
        } catch (ContainerExceptionInterface $e) {
            throw new ScopeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}

// TODO : fonction à renommer en make() ???? éventuellement faire une méthode plus générique nommée "factory()" et si on passe un paramétre de type classeName on appel la méthode make sur cette classe, sinon si il n'y a pas de paramétre on retour juste l'instance de la FactoryInterface ??? réfléchir pour voir si c'est une bonne idée !!!!
if (! function_exists('resolve')) {
    /**
     * Resolve a className from the container.
     *
     * @param  string $className
     * @param  array  $arguments
     *
     * @return mixed
     */
    function resolve(string $className, array $arguments = [])
    {
        return container(FactoryInterface::class)->make($className, $arguments);
    }
}

if (! function_exists('directory')) {
    /**
     * Get directory alias value.
     *
     * @param string $alias Directory alias, ie. "@config".
     *
     * @return string
     */
    function directory(string $alias): string
    {
        return container(Directories::class)->get($alias);
    }
}

if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        return container(Environment::class)->get($key, $default);
    }
}

if (! function_exists('configure')) {
    /**
     * Get the specified configuration object.
     *
     * @param string      $section
     * @param string|null $subset
     *
     * @return \Chiron\Config\ConfigInterface
     */
    function configure(string $section, ?string $subset = null): ConfigInterface
    {
        return container(Configure::class)->read($section, $subset);
    }
}

if (! function_exists('setting')) {
    /**
     * Get the specified value in the settings config.
     *
     * @param string $key
     *
     * @return mixed
     */
    function setting(string $key)
    {
        $config = container(SettingsConfig::class);

        if (! $config->has($key)) {
            throw new InvalidArgumentException(sprintf('The provided settings key [%s] doesn\'t exists.', $key));
        }

        return $config->get($key);
    }
}



/**
     * Looks for a string from possibilities that is most similar to value, but not the same (for 8-bit encoding).
     * @param  string[]  $possibilities
     */
// TODO : transformer cette fonction en fonction globale => cad virer le static et la renommer en get_suggestion() ou suggestion() ou alors stocker cette méthode dans une classe d'helper nommée Str::class ou Strings::class
// https://github.com/nette/utils/blob/master/src/Utils/Helpers.php#L59
/*
    public static function getSuggestion(array $possibilities, string $value): ?string
    {
        $best = null;
        $min = (strlen($value) / 4 + 1) * 10 + .1;
        foreach (array_unique($possibilities) as $item) {
            if ($item !== $value && ($len = levenshtein($item, $value, 10, 11, 10)) < $min) {
                $min = $len;
                $best = $item;
            }
        }
        return $best;
    }
*/
