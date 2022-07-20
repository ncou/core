<?php

declare(strict_types=1);

namespace Chiron\Core\Error\Debug\Node;

/**
 * Interface for Debugs
 *
 * Provides methods to look at contained value and iterate child nodes in the tree.
 */
interface NodeInterface
{
    /**
     * Get the child nodes of this node.
     *
     * @return array<\Cake\Error\Debug\NodeInterface>
     */
    public function getChildren(): array;

    /**
     * Get the contained value.
     *
     * @return mixed
     */
    public function getValue();
}
