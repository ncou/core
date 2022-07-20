<?php

declare(strict_types=1);

namespace Chiron\Core\Error\Debug\Node;

/**
 * Dump node for objects/class instances.
 */
class ClassNode implements NodeInterface
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var int
     */
    private $id;

    /**
     * @var array<\Cake\Error\Debug\PropertyNode>
     */
    private $properties = [];

    /**
     * Constructor
     *
     * @param string $class The class name
     * @param int $id The reference id of this object in the DumpContext
     */
    public function __construct(string $class, int $id)
    {
        $this->class = $class;
        $this->id = $id;
    }

    /**
     * Add a property
     *
     * @param \Cake\Error\Debug\PropertyNode $node The property to add.
     * @return void
     */
    public function addProperty(PropertyNode $node): void
    {
        $this->properties[] = $node;
    }

    /**
     * Get the class name
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->class;
    }

    /**
     * Get the reference id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get property nodes
     *
     * @return array<\Cake\Error\Debug\PropertyNode>
     */
    public function getChildren(): array
    {
        return $this->properties;
    }
}
