<?php

declare(strict_types=1);

namespace Chiron\Core;

use Chiron\Container\SingletonInterface;
use Chiron\Core\Exception\DirectoryException;
use InvalidArgumentException;
use Chiron\Core\Exception\MemoryException;
use Chiron\Filesystem\Filesystem;


// TODO : Nettoyage du cache : https://github.com/composer/composer/blob/5c98a2cf8e3df53172ed34130302a3d362f90631/src/Composer/Cache.php#L274

// TODO : commandes autour du cache :
//https://github.com/codeigniter4/CodeIgniter4/blob/b7ec33cd5618f2e4d31fbc2df2f95879c8b7e07a/system/Commands/Cache/InfoCache.php
//https://github.com/codeigniter4/CodeIgniter4/blob/b7ec33cd5618f2e4d31fbc2df2f95879c8b7e07a/system/Commands/Cache/ClearCache.php


// Memory.read($section) / .write($section) / .clear($section) / exists($section) et purge() pour supprimer TOUTES les sections ?

// TODO optimisation OPCache :
//https://github.com/symfony/cache/blob/6.1/Adapter/PhpFilesAdapter.php#L254
//https://github.com/voku/simple-cache/blob/master/src/voku/cache/AdapterOpCache.php#L125
//https://github.com/twigphp/Twig/blob/e33577f1beb6621a62fb7a2b45ec1a34d93b7714/src/Cache/FilesystemCache.php#L66

//https://github.com/php-fig/simple-cache/blob/master/src/CacheInterface.php

//https://github.com/yiisoft/cache-file/blob/master/src/FileCache.php
//https://github.com/drupal/core-file-cache/blob/8.8.x/FileCache.php

//https://github.com/illuminate/cache/blob/master/FileStore.php

//https://github.com/codeigniter4/CodeIgniter4/blob/60f1367c8ad16c4dea998a9d9a4bb1a7fd48c75c/system/Helpers/filesystem_helper.php#L293

//https://github.com/codeigniter4/CodeIgniter4/blob/2bee762276b9795b50f3b241d348626cfa0a24cc/system/Cache/Handlers/FileHandler.php
//https://github.com/symfony/cache-contracts/blob/2f7463f156cf9c665d9317e21a809c3bbff5754e/ItemInterface.php#L43
//https://github.com/symfony/symfony/blob/60ce5a3dfbd90fad60cd39fcb3d7bf7888a48659/src/Symfony/Component/Cache/CacheItem.php#L150

//https://github.com/spiral/boot/blob/master/src/Memory.php
//https://github.com/spiral/boot/blob/master/src/Bootloader/CoreBootloader.php#L46
//https://github.com/spiral/docs/blob/abe9833f65a52cc02e34eebf493f42594b5d37ea/framework/memory.md

//Vérifier qu'on peut faire un var_export de la valeur via ce bout de code (créer un MemoryException) :
//https://github.com/laminas/laminas-di/blob/fc8a5547db10908a25b03ed16452d22318b75090/src/Resolver/ValueInjection.php#L77


//Et voici un exemple pour hydrater une classe (trouvé dans le code de cakephp) :
/**
     * Set state magic method to support var_export
     *
     * This method helps for applications that want to implement
     * router caching.
     *
     * @param array<string, mixed> $fields Key/Value of object attributes
     * @return static A new instance of the route
     */
/*
    public static function __set_state(array $fields)
    {
        $class = static::class;
        $obj = new $class('');
        foreach ($fields as $field => $value) {
            $obj->$field = $value;
        }

        return $obj;
    }*/

/**
 * Long memory cache. Use this storage to remember results of your calculations, do not store user
 * or non-static data in here (!). It's a file based storage.
 */
// TODO : créer une interface MemoryInterface ????
// TODO : créer une méthode pour supprimer le fichier de mémoire. car actuellement on fait un save avec une valeur null pour simuler un effacement !!!!
final class Memory implements SingletonInterface
{
    // data file extension
    private const EXTENSION = 'php';

    private string $directory;
    private Filesystem $filesystem;

    public function __construct(string $directory)
    {
        // TODO : vérifier si le répertoire existe sinon il faudra le créer avec les droits mkdir/chmod 0775.
        // TODO : vérifier que le répertoire existe et qu'il est writable sinon lever une exception (cf méthode assertWritableDir de la classe DirectoriesBootloader). https://github.com/cakephp/cache/blob/master/Engine/FileEngine.php#L421
        $this->directory = rtrim($directory, '/'); //rtrim($path, '/\\');
        $this->filesystem = new Filesystem();
    }

    /**
     * Read data from long memory cache. Must return exacts same value as saved or null. Current
     * convention allows to store serializable (var_export-able) data.
     *
     * @param string $section Non case sensitive.
     *
     * @return mixed
     */
    public function read(string $section): mixed
    {
        $filename = $this->getFilename($section);

        // TODO : à virer ? et faire plutot un if not exists($section) throw MemoryException('Section xxx not found!')
        // TODO : sinon le plan B c'est de rajouter un paramétre $default dans la méthode et utiliser cette valeur si la section n'existe pas !!!!
        if (!file_exists($filename)) {
            return null;
        }

        try {
            return include($filename); // il faudrait pas faire un include_once ???? https://github.com/twigphp/Twig/blob/e33577f1beb6621a62fb7a2b45ec1a34d93b7714/src/Cache/FilesystemCache.php#L42
        } catch (\Throwable $e) {
            return null;
        }
    }

    // TODO : eventuellement retourner un booléen si le clear s'est bien passé. https://github.com/cakephp/cache/blob/c10b6073779129520a689e1b6fac2d7020ab95bc/CacheEngine.php#L281
    public function clear(string $section): void
    {
        try {
            $this->filesystem->unlink($this->getFilename($section));
        } catch (\Throwable $e) {
        }
    }

    // TODO : créer un tableau dans les propriétés de la classe $cache[] et socker le nom de la section et le fichier associé. Donc le exist pour vérifier d'abord dans le isset($this->cache[$section]).
    public function exists(string $section): bool
    {
        return $this->filesystem->exists($this->getFilename($section));
    }

    // TODO : créer un tableau dans les propriétés de la classe $cache[] et socker le nom de la section et le fichier associé. Donc le purge pourrait balayer toutes les clés pour supprimer les fichiers associés + faire un un unset() du tableau !!!
    // TODO : eventuellement renommer la méthode en reset()
    // TODO : méthode à virer, si on veux nettoyer le cache il faut utiliser la commande cache:clean qui va supprimer le répertoire ca sera plus simple.
    public function purge(): void
    {
        // TODO : à coder ? ou elle ne servira pas ?????
    }

    /**
     * Put data to long memory cache. No inner references or closures are allowed. Current
     * convention allows to store serializable (var_export-able) data.
     *
     * @param string $section Non case sensitive.
     * @param mixed  $value    Data should be exportable
     */
    // TODO : créer un tableau dans les propriétés de la classe $cache[] et socker le nom de la section et le fichier associé. Si on essaye d'enregistrer une seconde fois la section il faudrait lever une erreur. <=== ca a du sens de faire ce controle ????
    // TODO : lever une exception si le write n'a pas fonctionné !!!
    public function write(string $section, mixed $value): void
    {
        // Checks wether the value can be exported for code generation or not.
        if (! $this->isExportable($value)) {
            //https://github.com/laminas/laminas-di/blob/fc8a5547db10908a25b03ed16452d22318b75090/test/Resolver/ValueInjectionTest.php#L145
            throw new MemoryException('Unable to export value!');
        }

        //https://github.com/spiral/framework/blob/2.8/src/Files/src/Files.php#L140
        // TODO : il faudrait que le fichier créé soit avec un chmod de 0640
        // TODO : vérifier si la donnée est exportable (isExportable) : https://github.com/laminas/laminas-di/blob/fc8a5547db10908a25b03ed16452d22318b75090/src/Resolver/ValueInjection.php#L77
        // https://github.com/kenjis/ci4-attribute-routes/blob/1.x/src/AttributeRoutes/RouteFileGenerator.php#L77
        $this->filesystem->write(
            $this->getFilename($section),
            '<?php return ' . var_export($value, true) . ';'
        ); // // TODO : lever une exception si le write n'a pas fonctionné !!!

        // TODO : autre format : //return "<?php\n\nreturn ".var_export($messages->all($domain), true).";\n";

        // TODO : autre exemple : https://github.com/top-think/framework/blob/6.0/src/think/console/command/ServiceDiscover.php#L43
        //$header = '// This file is automatically generated at:' . date('Y-m-d H:i:s') . PHP_EOL . 'declare (strict_types = 1);' . PHP_EOL;
        //$content = '<?php ' . PHP_EOL . $header . "return " . var_export($services, true) . ';';
    }

    /**
     * Check if the provided value is exportable. For arrays it uses recursion.
     *
     * @see https://www.php.net/manual/en/language.oop5.magic.php#object.set-state
     *
     * @param mixed $value
     */
    //https://github.com/laminas/laminas-di/blob/fc8a5547db10908a25b03ed16452d22318b75090/test/Resolver/ValueInjectionTest.php#L160
    //https://github.com/laminas/laminas-di/blob/fc8a5547db10908a25b03ed16452d22318b75090/test/Resolver/ValueInjectionTest.php#L170
    private function isExportable(mixed $value): bool
    {
        if (is_scalar($value) || $value === null) {
            return true;
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                if (! $this->isExportable($item)) {
                    return false;
                }
            }

            return true;
        }

        if (is_object($value) && method_exists($value, '__set_state')) {
            $method = new ReflectionMethod($value, '__set_state');

            return $method->isStatic() && $method->isPublic();
        }

        return false;
    }

    /**
     * Get extension to use for runtime data or configuration cache.
     *
     * @param string $name Runtime data file name (without extension).
     *
     * @throws MemoryException When the name is not valid.
     */
    // https://developer.wordpress.org/reference/functions/sanitize_file_name/
    // https://gist.github.com/sumanthkumarc/2de2e2cc06c648a9f52c121501a181df
    // https://stackoverflow.com/questions/2021624/string-sanitizer-for-filename
    // http://www.touchoftechnology.com/simple-way-to-clean-up-filenames-in-php/
    // https://github.com/cakephp/filesystem/blob/505d549cd64b238a481a72a9650eed2f7841742c/File.php#L391
    // reserved characters pour de possibles extensions => https://www.php-fig.org/psr/psr-6/

    //https://github.com/symfony/cache-contracts/blob/2f7463f156cf9c665d9317e21a809c3bbff5754e/ItemInterface.php#L43
    //https://github.com/symfony/symfony/blob/60ce5a3dfbd90fad60cd39fcb3d7bf7888a48659/src/Symfony/Component/Cache/CacheItem.php#L150

    //https://github.com/symfony/symfony/blob/60ce5a3dfbd90fad60cd39fcb3d7bf7888a48659/src/Symfony/Component/Cache/Traits/FilesystemCommonTrait.php#L46
    //https://github.com/symfony/symfony/blob/60ce5a3dfbd90fad60cd39fcb3d7bf7888a48659/src/Symfony/Component/Cache/Traits/FilesystemCommonTrait.php#L124

    //https://github.com/codeigniter4/CodeIgniter4/blob/2bee762276b9795b50f3b241d348626cfa0a24cc/system/Cache/Handlers/BaseHandler.php#L55

    // TODO : eventuellement un simple urlencode() permet d'éviter le str_replace dans le code ci dessous !!!!

    //https://github.com/cakephp/cache/blob/master/Engine/FileEngine.php#L440
    //https://github.com/cakephp/cache/blob/c10b6073779129520a689e1b6fac2d7020ab95bc/CacheEngine.php#L345
    //https://github.com/cakephp/cache/blob/c10b6073779129520a689e1b6fac2d7020ab95bc/CacheEngine.php#L103

    private function getFilename(string $name): string
    {
        // TODO : sous windows vérifier que la longueur du chemin ne dépasse pas les 260 caractéres ????
        // TODO : créer une classe System pour stocker un test pour voir si on est sous windows !!!!
        // On Windows the whole path is limited to 260 chars
        /*
        if ('\\' === \DIRECTORY_SEPARATOR && \strlen($directory) > 260) {
            throw new \InvalidArgumentException(sprintf('Cache directory too long (%s).', $directory));
        }*/

        $this->ensureValidKey($name);

        // TODO : vérifier qu'on ne sort pas du répertoire via un '/../' par exemple !!!! <==== hummm pas sur que ce soit nécessaire car les caractéres '/' et '\' sont interdits !!!!
        //https://github.com/twigphp/Twig/blob/e33577f1beb6621a62fb7a2b45ec1a34d93b7714/src/Loader/FilesystemLoader.php#L268

        //Runtime cache
        return sprintf(
            '%s/%s.%s',
            $this->directory,
            strtolower(str_replace(['/', '\\'], '-', $name)), // TODO : à virer car le controle précédent interdit l'utilisation des caractéres '/' et '\' donc il n'y a rien à remplacer !!!
            self::EXTENSION
        );
    }



     /**
     * Ensure the validity of the given cache key.
     *
     * @param string $key Key to check.
     *
     * @return void
     *
     * @throws MemoryException When the key is invalid.
     */
     //https://github.com/spiral/twig-bridge/blob/master/src/TwigCache.php#L39
    private function ensureValidKey(string $key): void
    {
        if (strpos($key, "\0") !== false) {
            throw new MemoryException('A cache key cannot contain NUL bytes.');
        }

        if (strlen($key) === 0) {
            throw new MemoryException('A cache key must be a non-empty string.');
        }

        if (preg_match('/[\/\\<>?:|*"]/', $key)) {
            throw new MemoryException(
                "Cache key `{$key}` contains invalid characters. " .
                'You cannot use /, \\, <, >, ?, :, |, *, or " in cache keys.'
            );
        }

        // TODO : utiliser un controle plus simple du genre ci dessous, et permettre de a-Z ainsi que 0-9 et les caractéres "-" et "_" uniquement.
        /*
        if (preg_match('#[^-+_.A-Za-z0-9]#', $key, $match)) {
            throw new MemoryException(sprintf('Cache key contains "%s" but only characters in [-+_.A-Za-z0-9] are allowed.', $match[0])); // TODO : afficher aussi la valeur de $key
        }*/
    }

    // TODO : à déplacer dans une classe System::class du package support !!!
    // TODO : je pense que la derniére condition est fausse il faudrait plutot un truc du genre : function_exists && ((is_cli === false && opcache.enable) || (is_cli && opcacke.enable_cli))
    //https://github.com/twigphp/Twig/blob/e33577f1beb6621a62fb7a2b45ec1a34d93b7714/src/Cache/FilesystemCache.php#L66
    private function hasOpcache(): bool
    {
        // TODO : faire des "if" si ca permet de rendre le code plus lisible !!!!
        return \function_exists('opcache_invalidate')
        && filter_var(ini_get('opcache.enable'), \FILTER_VALIDATE_BOOLEAN)
        && (!\in_array(\PHP_SAPI, ['cli', 'phpdbg'], true) || filter_var(ini_get('opcache.enable_cli'), \FILTER_VALIDATE_BOOLEAN));

/*
        self::$hasCompileFileFunction = (
                \function_exists('opcache_compile_file')
                &&
                !empty(@\opcache_get_status()) //If the opcache is disabled, this functions returns false.
            );*/
    }

    //https://github.com/twigphp/Twig/blob/e33577f1beb6621a62fb7a2b45ec1a34d93b7714/src/Loader/FilesystemLoader.php#L230
    private function normalizeName(string $name): string
    {
        return preg_replace('#/{2,}#', '/', str_replace('\\', '/', $name));
    }

}
