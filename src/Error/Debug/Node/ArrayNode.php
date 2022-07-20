<?php

declare(strict_types=1);

namespace Chiron\Core\Error\Debug\Node;

/**
 * Dump node for Array values.
 */
class ArrayNode implements NodeInterface
{
    /**
     * @var array<\Cake\Error\Debug\ArrayItemNode>
     */
    private $items;

    /**
     * Constructor
     *
     * @param array<\Cake\Error\Debug\ArrayItemNode> $items The items for the array
     */
    public function __construct(array $items = [])
    {
        $this->items = [];
        foreach ($items as $item) {
            $this->add($item);
        }
    }

    /**
     * Add an item
     *
     * @param \Cake\Error\Debug\ArrayItemNode $node The item to add.
     * @return void
     */
    public function add(ArrayItemNode $node): void
    {
        $this->items[] = $node;
    }

    /**
     * Get the contained items
     *
     * @return array<\Cake\Error\Debug\ArrayItemNode>
     */
    public function getValue(): array
    {
        return $this->items;
    }

    /**
     * Get Item nodes
     *
     * @return array<\Cake\Error\Debug\ArrayItemNode>
     */
    public function getChildren(): array
    {
        return $this->items;
    }
}
