<?php

declare(strict_types=1);

namespace Chiron\Core\Facade;

//https://github.com/lizhichao/one/blob/master/src/Facades/Facade.php

//https://github.com/top-think/framework/blob/6.0/src/think/Facade.php

/**
 * You must override the function "getFacadeAccessor" in your class and return the Container alias key used to retrieve the service.
 */
// TODO : ajouter une méthode pour insérer un ContainerInterface dans cette classe et ne plus utiliser directement la fonction "container()" mais passer par l'objet qu'on aura injecté. Il faudra aussi prévoir une classe de boot pour injecter ce container. Genre ajouter un ContainerAwareInterface à cette classe. exemple :        https://github.com/laravel/framework/blob/1bbe5528568555d597582fdbec73e31f8a818dbc/src/Illuminate/Foundation/Bootstrap/RegisterFacades.php#L22
abstract class AbstractFacade extends AbstractFacadeProxy
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
     * getInstance.
     *
     * @param bool $forceNew
     *
     * @return mixed
     */
    // TODO : forcer le type de retour à "object" attention il faut une version minimale de PHP 7.3 pour utiliser cette notation !!!!
    // TODO : éventuellement faire un check is_object et lever une RuntimeException (ou exception consom si besoin) si le type obtenu depuis le container n'est pas le bon !!!! + ajouter dans la dochead le @throw RuntimeException par exemple.
    // TODO : vérifier l'utilité du $forceNew je pense que ca ne sert à rien !!!! ou alors le mettre en variable de classe en protected genre $this->forceNew pour qu'il soit overridé si besoin.
    public static function getInstance(bool $forceNew = false)
    {
        return container(static::getFacadeAccessor(), $forceNew); // TODO : ameliorer ce code ce n'est pas tres propre
    }

    /**
     * Get the registered name of the component in the container.
     *
     * @return string
     */
    abstract protected static function getFacadeAccessor(): string;
}
