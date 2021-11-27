<?php

declare(strict_types=1);

namespace Chiron\Core\Facade;

//https://github.com/lizhichao/one/blob/master/src/Facades/Facade.php

/**
 * You must override the function "getFacadeAccessor" in your class and return the Container alias key used to retrieve the service.
 */
abstract class AbstractFacadeProxy
{
    /**
     * Prevent the instanciation of the class. Use only static calls.
     */
    private function __construct()
    {
        // TODO : passer le constructeur en public et lever ce type d'exception : throw new \Error('Class ' . static::class . ' is static and cannot be instantiated.');
        // cf    https://github.com/nette/utils/blob/master/src/StaticClass.php#L24
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param string $method    The method name.
     * @param array  $arguments The arguments of method call.
     *
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments)
    {
        // TODO : sécuriser les appels vers les méthodes privées/protected ????
        // https://github.com/nette/utils/blob/master/src/StaticClass.php#L34
        // https://github.com/nette/utils/blob/a828903f85bb513e51ba664b44b61f20d812cf20/src/Utils/ObjectHelpers.php#L90

        $instance = static::getInstance();

        return $instance->$method(...$arguments); // TODO : gérer le cas ou la méthode n'existe pas (et dans ce cas faire une suggestion via levenshtein) ???
    }

    // TODO : forcer le type de retour à "object" attention il faut une version minimale de PHP 7.3 pour utiliser cette notation !!!!
    // TODO : éventuellement faire un check is_object et lever une RuntimeException (ou exception consom si besoin) si le type obtenu depuis le container n'est pas le bon !!!! + ajouter dans la dochead le @throw RuntimeException par exemple.
    abstract public static function getInstance();
}
