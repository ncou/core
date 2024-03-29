<?php

declare(strict_types=1);

namespace Chiron\Core;

use Chiron\Container\SingletonInterface;
use Chiron\Core\Exception\EnvironmentException;
use InvalidArgumentException;

// TODO : il faudrait surement une méthode pour renvoyer toutes les clés/valeurs du stype "toArray()" ou "all()"
// TODO : https://github.com/symfony/symfony/blob/87c9ab4cbe89e90236a453718575e2b93fe5f88b/src/Symfony/Component/Dotenv/Dotenv.php#L190
// TODO : il faut aussi gérer les cas ou il y a des double quottes au début/fin de la chaine.
// TODO : inspiration :   https://github.com/arrilot/dotenv-php/blob/master/src/DotEnv.php
//https://github.com/sebastiansulinski/dotenv/blob/master/src/DotEnv/DotEnv.php#L109

//https://github.com/silverstripe/silverstripe-framework/blob/4/src/Core/Environment.php
//https://github.com/silverstripe/silverstripe-framework/blob/4/src/Core/EnvironmentLoader.php
//https://github.com/vlucas/phpdotenv/blob/master/src/Loader.php
//https://github.com/laravel/lumen-framework/blob/5.8/src/Bootstrap/LoadEnvironmentVariables.php#L52

//https://github.com/vlucas/phpdotenv/blob/master/src/Environment/Adapter/ApacheAdapter.php
//https://github.com/vlucas/phpdotenv/blob/master/src/Environment/Adapter/EnvConstAdapter.php
//https://github.com/vlucas/phpdotenv/blob/master/src/Environment/Adapter/PutenvAdapter.php
//https://github.com/vlucas/phpdotenv/blob/master/src/Environment/Adapter/ServerConstAdapter.php

//https://github.com/pn-neutrino/dotenv/blob/master/src/Dotenv.php
//https://github.com/oscarotero/env/blob/master/src/Env.php

//https://github.com/chillerlan/php-dotenv/blob/master/src/DotEnv.php#L144

//https://github.com/spiral/roadrunner-worker/blob/1a10a7641ff045fff2570b5ec1a265713e5a1bd5/src/Environment.php#L84

// TODO : permettre de faire un getIterator sur cette classe, idem pour utiliser un ArrayAccess pour utiliser cette classe comme un tableau !!!!
// TODO : ajouter dans cette classe une méthode pour vérifier si on est en mode console (cad is_cli) + ajouter cela dans le fichier functions.php
// TODO : il va falloir que cette classe soit en shared si on souhaite ajouter des key/data dans cette classe sans forcément les ajouter dans $ENV et $SERVER !!!!!
// TODO : créer une méthode add() qui attend un tableau de valeurs et qui fait un arra_merge avec le tableau $this->values ????
// TODO : permettre d'utiliser les helpers ArrayAccess pour faire un truc du genre "$directories['config']"
final class Environment implements SingletonInterface
{
    // TODO : utiliser des constantes pour initialiser la valeur dans le fichier de config Settings.environment = Environment::PRODUCTION
    //https://github.com/opulencephp/Opulence/blob/4254ea09135538252b0919316cb8cef0037841e4/src/Opulence/Environments/Environment.php#L19
    /** The production environment */
    //public const PRODUCTION = 'production';
    /** The staging environment */
    //public const STAGING = 'staging';
    /** The testing environment */
    //public const TESTING = 'testing';
    /** The development environment */
    //public const DEVELOPMENT = 'development';

    private array $values = [];

    public function __construct(array $values = [])
    {
        $this->init($values);
    }

    /**
     * Prepare the environment vars (top priority to $value, followed by $_ENV and last priority $_SERVER)
     *
     * @param array $values
     */
    public function init(array $values = []): void
    {
        $this->values = [];
        $this->add(array_merge($_SERVER, $_ENV, $values));
    }

    /**
     * @return self
     */
    /*
    public static function fromGlobals(): self
    {
        $env = \array_merge($_ENV, $_SERVER);

        return new self($env);
    }*/

    public function all(): array
    {
        return $this->values;
    }

    /**
     * Gets the value of an environment variable.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    // TODO : tester en passant des chaines vides aux méthodes get() et set() pour voir comment ca réagit et éventuellement il faudra lever des InvalidArgumentException !!!
    public function get(string $name, $default = null)
    {
        if ($this->has($name)) {
            return $this->values[$name];
        }

        return $default;
    }

    public function has(string $name): bool
    {
        return isset($this->values[$name]);
    }

    /**
     * @param array $values An array with environments names as keys and datas as values
     */
    public function add(array $values): void
    {
        foreach ($values as $name => $value) {
            if (! is_string($name)) {
                throw new EnvironmentException(sprintf('Method "%s()" expects an associative array.', __METHOD__));
            }
            $this->set($name, $value);
        }
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function set(string $name, $value): void
    {
        // empty name value is not logical !
        if ($name === '') {
            throw new EnvironmentException('Environment names must be a non empty string.');
        }

        $this->values[$name] = self::normalize($value);
    }

    /**
     * Normalize the value of the specified environment variable, translating
     * values of 'true', 'false', 'empty', and 'null' (case-insensitive) to their
     * actual non-string values and strip the starting and ending quotes.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    // TODO : regarder ici si cela peut servir !!!! => https://github.com/viserio/dotenv-bridge/blob/master/Env.php#L124
    // TODO : exemple en javascript : https://github.com/sindresorhus/yn/blob/master/index.js#L14
    // TODO : ajouter pour true => 'y' / 'yes' / '1' / 'on'
    // TODO : ajouter pour false => 'n' / 'no' / '0' / 'off'
    // TODO : il faudrait même ajouter pour le true : 'ok' et pour le false : 'ko'
    // TODO : virer la vérification avec les parenthéses !!!! style '(empty)' ou '(true)'
    // TODO : BOOLEAN_TRUE_STRINGS= ('true', 'on', 'ok', 'y', 'yes', '1')
    private static function normalize($value)
    {
        if (! is_string($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return $value + 0;
        }

        if (preg_match('/^base64:/', $value) === 1) {
            return base64_decode(substr($value, 7), true); // TODO : attention si on ajoute une string en base64 invalide cela va retourner un booléen, il faudrait plutot lever une exception !!!! Voir même utiliser une classe Support\Base64::class qui léverai directement l'erreur si la chaine base64 n'est pas valide !!!!
        }

        switch (strtolower($value)) {
            case 'true':
            case 'yes':
            case 'on':
                return true;
            case 'false':
            case 'no':
            case 'off':
                return false;
            case 'null':
                return null;
            case 'empty':
                return '';
        }

        // strip starting & ending quotes (single or double)
        if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
            return $matches[2];
        }

        return $value;
    }
}
