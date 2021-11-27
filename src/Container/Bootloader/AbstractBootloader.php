<?php

declare(strict_types=1);

namespace Chiron\Core\Container\Bootloader;

use Chiron\Injector\InvokerInterface;
use Closure;
use Psr\Container\ContainerInterface;

// TODO : Lever une BootException si la méthode 'boot()' n'est pas implémentée dans la classe mére. Attention à la visibilité, soit on choisi du public (mais dans ce cas le fromCallable ne sert à rein !!!!) soit du private/protected.
// TODO : il faudrait pas faire un trait dans le package core par exempe InvokableTrait qui aurait une méthode protected invoke($callable, $wrap = false) avec le $wrap qui permet d'encapsuler le callable dans une Closure::fromCallable.
abstract class AbstractBootloader implements BootloaderInterface
{
    // TODO : stocker le container dans une variable protected de la classe ce qui permettrait d'y accéder via un $this->container. Voir meême créer une méthode getContainer. Faire la même chose pour le invoker ? si on souhaite par exemple executer un sous executable de la classe par exemple la méthode boot() pourrait executer la méthode bootA() et ensuite bootB() en cascade.
    // TODO : créer une méthode protected 'boot()' qui retournerait une exception pour indiquer que cette méthode n'est pas implémentée dans la classe mére ? (cad 'overidden' en anglais). Faire la méme chose pour les classes AbstractCommand et AbstractServiceProvider ????
    public function bootload(InvokerInterface $invoker): void
    {
        //$injector = new Injector($container);

        // TODO : ajouter un \Closure::fromCallable() car la méthode boot peut être protected, voir private !!! (idem pour la classe AbstractCommand !!!!)
        // TODO : lever une exception si la méthode boot n'est pas présente !!!!
        // TODO : il faudrait surement faire un try/catch autour de la méthode call, car si la méthode boot n'existe pas une exception sera retournée. Une fois le catch fait il faudra renvoyer une new BootloadException($e->getMessage()), pour convertir le type d'exception (penser à mettre le previous exception avec la valeur $e).
        $invoker->invoke(Closure::fromCallable([$this, 'boot']));

        // TODO : utiliser ce bout de code, dans le cas ou on passe un tableau (et que is_callable est false) + le 1er element du tableau est un objet + method_exists est valide pour 1er element du tableau (la classe) & le 2eme element du tableau le nom de la méthode (qui doit être une string). ATTENTION cependant au scope qui ne fonctionnera peut etre pas !!!!
        /*
        $reflection = new \ReflectionClass($this);
        $closure = $reflection->getMethod('boot')->getClosure($this);
        $invoker->invoke($closure);
        */
    }

    // TODO : créer une méthode protected 'boot' dans cette classe qui léve une exception, donc si l'utilisateur n'a pas fait un override de cette méthode boot dans sa classe c'est la méthode ici qui prendra le relais et donc lévera une exception. Faire la même chose pour les classes abstraites de command/config...etc
    /*
    protected function boot()
    {
        throw new \LogicException('You need to define the "boot" method in you class');

    }*/
}
