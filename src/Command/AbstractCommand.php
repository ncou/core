<?php

declare(strict_types=1);

namespace Chiron\Core\Command;

use Chiron\Injector\Injector;
use LogicException;
use Psr\Container\ContainerInterface;
use Chiron\Console\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Chiron\Console\Traits\InputHelpersTrait;
use Chiron\Console\Traits\OutputHelpersTrait;
use Chiron\Console\Traits\CallCommandTrait;
use Closure;

use Chiron\Injector\InjectorAwareTrait;
use Chiron\Injector\InjectorAwareInterface;

// TODO : eventuellement désactiver la méthode setName() cad lui faire lever une exception si elle est utilisée. Pour s'assurer qu'on récupére le nom de la commande UNIQUEMENT via de la reflection d'attibut ou de variable de classe.

//https://github.com/spiral/console/blob/master/src/Command.php
//https://github.com/spiral/console/blob/master/src/Traits/HelpersTrait.php

//https://github.com/viserio/console/blob/master/Command/AbstractCommand.php
//https://github.com/leevels/console/blob/master/Command.php

//https://github.com/illuminate/console/blob/master/Command.php
//https://github.com/symfony/console/blob/master/Command/Command.php

//https://github.com/illuminate/console/blob/6.x/Concerns/InteractsWithIO.php


//Style des tableaux avec simple ou double bordure =>   https://github.com/symfony/console/blob/master/Helper/Table.php#L808


//https://github.com/symfony/console/blob/master/Style/SymfonyStyle.php#L100

// TESTS !!!!!!!!
//https://github.com/laravel/framework/blob/7.x/tests/Console/CommandTest.php


/**
 * Provides automatic command configuration and access to global container scope.
 */
// TODO : ajouter le containerAwareTrait + ContainerAwareInterface !!!!
// TODO : on ne peut pas ajouter une fonction abstraite "perform" car le constructeur n'est pas le même selon la classe. Réfléchir cependant à mettre dans cette classe une fonction protected perform qui throw une exception, cela éviterai un check si la méthode existe. Mais voir si cela fonctionne quand la signature de perform définiée dans la classe mére est différente, on risque d'avoir le même probléme qu'avec la signature de fonction abstraite !!!
abstract class AbstractCommand extends BaseCommand
{
    private Injector $injector;

    public function __construct(Injector $injector)
    {
        parent::__construct(name: null);
        $this->injector = $injector;
    }

    /**
     * Store the input and output object, and 'Perform()' the console command.
     *
     * @param InputInterface   $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        // TODO : lever une exception si la méthode 'perform()' n'est pas présente !!!!
        // TODO : lever une logicexception si la méthode 'perform' n'est pas trouvée dans la classe mére ? (voir même une CommandException)

        // TODO : il faudrait surement faire un try/catch autour de la méthode call, car si la méthode perform n'existe pas une exception sera retournée. Une fois le catch fait il faudra renvoyer une new CommandException($e->getMessage()), pour convertir le type d'exception (penser à mettre le previous exception avec la valeur $e).
        $result = $this->injector->invoke(Closure::fromCallable([$this, 'perform']));

        // Try to convert the returned value to something logical.
        if (is_int($result) && $result >= 0 && $result <= 255) {
            return $result;
        }
        if ($result === null || $result === true) {
            return self::SUCCESS;
        }

        return self::FAILURE;
    }


    /**
     * Configures the command.
     */
    /*
    protected function configure(): void
    {
        $this->setName(static::NAME);
        $this->setDescription(static::DESCRIPTION);

        foreach ($this->defineOptions() as $option) {
            call_user_func_array([$this, 'addOption'], $option);
        }

        foreach ($this->defineArguments() as $argument) {
            call_user_func_array([$this, 'addArgument'], $argument);
        }
    }*/



    /**
     * Define command options.
     *
     * @return array
     */
    /*
    protected function defineOptions(): array
    {
        return static::OPTIONS;
    }*/

    /**
     * Define command arguments.
     *
     * @return array
     */
    /*
    protected function defineArguments(): array
    {
        return static::ARGUMENTS;
    }*/
}

