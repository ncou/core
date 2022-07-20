<?php
declare(strict_types=1);

namespace Chiron\Core\Error\Debug\Formatter;

use Chiron\Core\Error\Debug\Node\NodeInterface;

/**
 * Interface for formatters used by Debugger::exportVar()
 *
 * @unstable This interface is not stable and may change in the future.
 */
interface FormatterInterface
{
    /**
     * Convert a tree of NodeInterface objects into a plain text string.
     *
     * @param \Cake\Error\Debug\NodeInterface $node The node tree to dump.
     * @return string
     */
    public function dump(NodeInterface $node): string;

    /**
     * Output a dump wrapper with location context.
     *
     * @param string $contents The contents to wrap and return
     * @param array $location The file and line the contents came from.
     * @return string
     */
    public function formatWrapper(string $contents, array $location): string;
}
