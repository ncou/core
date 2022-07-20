<?php

declare(strict_types=1);

use Chiron\Config\ConfigInterface;
use Chiron\Container\Container;
use Chiron\Injector\FactoryInterface;
use Chiron\Config\Configure;
use Chiron\Core\Directories;
use Chiron\Core\Environment;
use Chiron\Core\Exception\ScopeException;
use Psr\Container\ContainerExceptionInterface;
use Chiron\Config\SettingsConfig;
use Chiron\Core\Error\Debugger;

// TODO : créer des helpers dans twid pour utiliser la fonction dump/pr/pj dans les templates twig !!!
//https://github.com/YepFoundation/tracy-twig-extensions/blob/master/src/DumpExtension.php
//https://github.com/bearlikelion/twig-debugbar/blob/master/Extension.php
//https://github.com/nlemoine/twig-dump-extension/tree/master/src

//https://github.com/rsanchez/laravel-framework/blob/master/src/Illuminate/Foundation/helpers.php#L147
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
        return container(FactoryInterface::class)->build($className, $arguments);
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

// TODO : attention cela risque de ne pas fonctionner si on essaye de lire un fichier de configuration qui n'a pas été publié via le "publisher", il faudrait ajouter une initialisation des champs dans la classe configure pour mettre les valeurs par défaut des config qu'on n'a pas encore copié !!!!
// TODO : utiliser cette fonction pour splitter la clés via le point "." par exemple pour récupérer une clés du stype 'http.default_charset' => https://github.com/windwalker-io/utilities/blob/master/src/StrNormalize.php#L131
//https://github.com/rsanchez/laravel-framework/blob/master/src/Illuminate/Foundation/helpers.php#L147
if (! function_exists('config')) {
    /**
     * Get the specified configuration object.
     *
     * @param string      $section
     *
     * @return \Chiron\Config\ConfigInterface
     */
    function config(string $section): ConfigInterface
    {
        $data = container(Configure::class)->read($section);

        return new \Chiron\Config\Config($data);
    }
}

//https://github.com/rsanchez/laravel-framework/blob/master/src/Illuminate/Foundation/helpers.php#L147
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


//https://github.com/cakephp/cakephp/blob/4.x/src/Core/functions.php#L41
//https://github.com/cakephp/cakephp/blob/4.x/tests/TestCase/Core/FunctionsTest.php#L61
//https://github.com/illuminate/support/blob/master/helpers.php#L101
//https://github.com/yiisoft/html/blob/master/src/Html.php#L174
if (! function_exists('e')) {
    /**
     * Convenience method for htmlspecialchars.
     *
     * @param mixed       $text    Text to wrap through htmlspecialchars. Also works with arrays, and objects.
     *             Arrays will be mapped and have all their elements escaped. Objects will be string cast if they
     *             implement a `__toString` method. Otherwise the class name will be used.
     *             Other scalar types will be returned unchanged.
     * @param bool        $double  Encode existing html entities.
     * @param string|null $charset Character set to use when escaping.
     *   Defaults to config value in `mb_internal_encoding()` or 'UTF-8'.
     *
     * @return mixed Wrapped text.
     *
     * @link https://book.cakephp.org/4/en/core-libraries/global-constants-and-functions.html#h
     */
    function e(mixed $text, bool $double = true, ?string $charset = null): mixed
    {
        if (is_string($text)) {
            //optimize for strings
        } elseif (is_array($text)) {
            $texts = [];
            foreach ($text as $k => $t) {
                $texts[$k] = e($t, $double, $charset);
            }

            return $texts;
        } elseif (is_object($text)) {
            if ($text instanceof Stringable) {
                $text = (string)$text;
            } else {
                $text = '(object)' . get_class($text);
            }
        } elseif ($text === null || is_scalar($text)) {
            return $text;
        }

        static $defaultCharset = false;
        if ($defaultCharset === false) {
            $defaultCharset = mb_internal_encoding() ?: 'UTF-8';
        }

        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, $charset ?: $defaultCharset, $double);
    }
}

//https://github.com/cakephp/cakephp/blob/5.x/src/basics.php
if (!function_exists('debug')) {
    /**
     * Prints out debug information about given variable and returns the
     * variable that was passed.
     *
     * Only runs if debug mode is enabled.
     *
     * @param mixed $var Variable to show debug information for.
     * @param bool|null $showHtml If set to true, the method prints the debug data in a browser-friendly way.
     * @param bool $showFrom If set to true, the method prints from where the function was called.
     * @return mixed The same $var that was passed
     * @link https://book.cakephp.org/4/en/development/debugging.html#basic-debugging
     * @link https://book.cakephp.org/4/en/core-libraries/global-constants-and-functions.html#debug
     */
    function debug(mixed $var, ?bool $showHtml = null, bool $showFrom = true): mixed
    {
        //if (!Configure::read('debug')) {
        //    return $var;
        //}

        $location = [];
        if ($showFrom) {
            $trace = Debugger::trace(['start' => 1, 'depth' => 2, 'format' => 'array']);
            /** @psalm-suppress PossiblyInvalidArrayOffset */
            $location = [
                'line' => $trace[0]['line'],
                'file' => $trace[0]['file'],
            ];
        }

        Debugger::printVar($var, $location, $showHtml);

        return $var;
    }

}

// TODO : regarder ici les équivalence pour éviter d'avoir l'erreur des "headers already send" lorsqu'on fait un echo ou un printf
//https://github.com/windwalker-io/framework/blob/9159d8ec7cbdd056d26db10186b9bfaa30327b42/packages/utilities/src/functions.php#L33
//https://github.com/windwalker-io/framework/blob/9159d8ec7cbdd056d26db10186b9bfaa30327b42/packages/utilities/src/Arr.php#L1141

if (!function_exists('stackTrace')) {
    /**
     * Outputs a stack trace based on the supplied options.
     *
     * ### Options
     *
     * - `depth` - The number of stack frames to return. Defaults to 999
     * - `args` - Should arguments for functions be shown? If true, the arguments for each method call
     *   will be displayed.
     * - `start` - The stack frame to start generating a trace from. Defaults to 1
     *
     * @param array<string, mixed> $options Format for outputting stack trace
     * @return void
     */
    function stackTrace(array $options = []): void
    {
        //if (!Configure::read('debug')) {
        //    return;
        //}

        $options += ['start' => 0];
        $options['start']++;

        /** @var string $trace */
        $trace = Debugger::trace($options);
        echo $trace;
    }

}

if (!function_exists('dd')) {
    /**
     * Prints out debug information about given variable and dies.
     *
     * Only runs if debug mode is enabled.
     * It will otherwise just continue code execution and ignore this function.
     *
     * @param mixed $var Variable to show debug information for.
     * @param bool|null $showHtml If set to true, the method prints the debug data in a browser-friendly way.
     * @return void
     * @link https://book.cakephp.org/4/en/development/debugging.html#basic-debugging
     */
    function dd(mixed $var, ?bool $showHtml = null): void
    {
        //if (!Configure::read('debug')) {
        //    return;
        //}

        $trace = Debugger::trace(['start' => 1, 'depth' => 2, 'format' => 'array']);
        /** @psalm-suppress PossiblyInvalidArrayOffset */
        $location = [
            'line' => $trace[0]['line'],
            'file' => $trace[0]['file'],
        ];

        Debugger::printVar($var, $location, $showHtml);
        die(1);
    }
}

//https://github.com/cakephp/cakephp/blob/5.x/src/Core/functions.php
if (!function_exists('pr')) {
    /**
     * print_r() convenience function.
     *
     * In terminals this will act similar to using print_r() directly, when not run on CLI
     * print_r() will also wrap `<pre>` tags around the output of given variable. Similar to debug().
     *
     * This function returns the same variable that was passed.
     *
     * @param mixed $var Variable to print out.
     * @return mixed the same $var that was passed to this function
     * @link https://book.cakephp.org/4/en/core-libraries/global-constants-and-functions.html#pr
     * @see debug()
     */
    function pr(mixed $var): mixed
    {
        //if (!Configure::read('debug')) {
        //    return $var;
        //}

        $template = PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg' ? '<pre class="pr">%s</pre>' : "\n%s\n\n";
        printf($template, trim(print_r($var, true)));

        return $var;
    }

}

if (!function_exists('pj')) {
    /**
     * JSON pretty print convenience function.
     *
     * In terminals this will act similar to using json_encode() with JSON_PRETTY_PRINT directly, when not run on CLI
     * will also wrap `<pre>` tags around the output of given variable. Similar to pr().
     *
     * This function returns the same variable that was passed.
     *
     * @param mixed $var Variable to print out.
     * @return mixed the same $var that was passed to this function
     * @see pr()
     * @link https://book.cakephp.org/4/en/core-libraries/global-constants-and-functions.html#pj
     */
    function pj(mixed $var): mixed
    {
        //if (!Configure::read('debug')) {
        //    return $var;
        //}

        $template = PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg' ? '<pre class="pj">%s</pre>' : "\n%s\n\n";
        printf($template, trim(json_encode($var, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));

        return $var;
    }

}
