<?php

declare(strict_types=1);

namespace Chiron\Core;

use Chiron\Container\SingletonInterface;
use InvalidArgumentException;

/**
 * Manage core debug and contains some public informations like framework name or version.
 */
final class Core implements SingletonInterface
{
    public const NAME = 'ChironPHP';

    public const VERSION = '1.0.0';

    /**
     * Chiron terminal logo.
     *
     * @see http://patorjk.com/software/taag/#p=display&f=Slant&t=Chiron%201.0
     */
    public const LOGO = "
   ________    _                     ___ ____
  / ____/ /_  (_)________  ____     <  // __ \
 / /   / __ \/ / ___/ __ \/ __ \    / // / / /
/ /___/ / / / / /  / /_/ / / / /   / // /_/ /
\____/_/ /_/_/_/   \____/_/ /_/   /_(_)____/
";

    /**
     * Chiron terminal logo small.
     *
     * @see http://patorjk.com/software/taag/#p=display&f=Small%20Slant&t=Chiron%201.0
     */
    public const LOGO_SMALL = "
  _______   _                 ___ ___
 / ___/ /  (_)______  ___    <  // _ \
/ /__/ _ \/ / __/ _ \/ _ \   / // // /
\___/_//_/_/_/  \___/_//_/  /_(_)___/
";

    /**
     * Chiron server start banner logo.
     */
    public const BANNER_LOGO = "
  _______   _                 ____                                   __
 / ___/ /  (_)______  ___    / __/______ ___ _  ___ _    _____  ____/ /__
/ /__/ _ \/ / __/ _ \/ _ \  / _// __/ _ `/  ' \/ -_) |/|/ / _ \/ __/  '_/
\___/_//_/_/_/  \___/_//_/ /_/ /_/  \_,_/_/_/_/\__/|__,__/\___/_/ /_/\_\
";

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }
}
