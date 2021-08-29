<?php

declare(strict_types=1);

namespace Chiron\Core\Engine;

interface EngineInterface
{
    /**
     * Execute the action (could be a console command or a route command).
     *
     * @return mixed The return value could be an int for the console engine or void for the http engine
     */
    public function ignite();

    /**
     * Check if the engine is valid given the current context.
     *
     * @return bool
     */
    public function isActive(): bool;
}
