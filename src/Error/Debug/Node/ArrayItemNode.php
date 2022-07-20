<?php

declare(strict_types=1);

namespace Chiron\Core\Error\Debug\Node;

/**
 * Dump node for Array Items.
 */
class ArrayItemNode implements NodeInterface
{
    /**
     * @var \Cake\Error\Debug\NodeInterface
     */
    private $key;

    /**
     * @var \Cake\Error\Debug\NodeInterface
     */
    private $value;

    /**
     * Constructor
     *
     * @param \Cake\Error\Debug\NodeInterface $key The node for the item key
     * @param \Cake\Error\Debug\NodeInterface $value The node for the array value
     */
    public function __construct(NodeInterface $key, NodeInterface $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * Get the value
     *
     * @return \Cake\Error\Debug\NodeInterface
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the key
     *
     * @return \Cake\Error\Debug\NodeInterface
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @inheritDoc
     */
    public function getChildren(): array
    {
        return [$this->value];
    }
}
