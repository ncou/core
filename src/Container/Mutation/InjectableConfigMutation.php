<?php

declare(strict_types=1);

namespace Chiron\Core\Container\Mutation;

use Chiron\Config\InjectableConfigInterface;
use Chiron\Container\Container;
use Chiron\Core\Configure;

// TODO : Ne pas utiliser la facade "Configure::class" c'est pas trés propre !!!

// TODO : déplacer cette classe dans le package chiron/config
final class InjectableConfigMutation
{
    public static function mutation(InjectableConfigInterface $config)
    {
        $section = $config->getConfigSectionName();

        $configure = container(Configure::class);

        // TODO : utiliser directement la fonction 'configure($section, $subset)' et si la réponse n'est pas null alors faire un setData !!!!
        if ($configure->hasConfig($section)) {
            // the section subset could be empty.
            $subset = $config->getSectionSubsetName();
            // get the data array for section and subset-section.
            $data = $configure->getConfigData($section, $subset);

            // inject in the config the configuration file data.
            // TODO : il faudra peut etre faire un try/catch et transformer l'exception en ApplicationException. Je pense que si on injecte des mauvaises données on aura une exception car le schema sera invalid.
            $config->setData($data);
        }
    }
}
