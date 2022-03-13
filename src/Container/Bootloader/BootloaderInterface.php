<?php

declare(strict_types=1);

namespace Chiron\Core\Container\Bootloader;

use Chiron\Injector\InvokerInterface;

interface BootloaderInterface
{
    // TODO : ne pas avoir de paramétre pour cette méthode bootload() ce n'est pas logique de devoir lui passer un invoker, si l'utilisateur fait sa propre classe de bootload il ne va pas forcément utiliser le invoker, il faut rester générique !!!!!
    public function bootload(InvokerInterface $invoker): void;
}
