<?php

declare(strict_types=1);

namespace Chiron\Core\Error\Debug\Node;

/**
 * Dump node for class references.
 *
 * To prevent cyclic references from being output multiple times
 * a reference node can be used after an object has been seen the
 * first time.
 */
class ReferenceNode implements NodeInterface
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
     * Constructor
     *
     * @param string $class The class name
     * @param int $id The id of the referenced class.
     */
    public function __construct(string $class, int $id)
    {
        $this->class = $class;
        $this->id = $id;
    }

    /**
     * Get the class name/value
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->class;
    }

    /**
     * Get the reference id for this node.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getChildren(): array
    {
        return [];
    }
}
