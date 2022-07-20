<?php

declare(strict_types=1);

namespace Chiron\Core\Error\Debug\Node;

/**
 * Dump node for object properties.
 */
class PropertyNode implements NodeInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $visibility;

    /**
     * @var \Cake\Error\Debug\NodeInterface
     */
    private $value;

    /**
     * Constructor
     *
     * @param string $name The property name
     * @param string|null $visibility The visibility of the property.
     * @param \Cake\Error\Debug\NodeInterface $value The property value node.
     */
    public function __construct(string $name, ?string $visibility, NodeInterface $value)
    {
        $this->name = $name;
        $this->visibility = $visibility;
        $this->value = $value;
    }

    /**
     * Get the value
     *
     * @return \Cake\Error\Debug\NodeInterface
     */
    public function getValue(): NodeInterface
    {
        return $this->value;
    }

    /**
     * Get the property visibility
     *
     * @return string
     */
    public function getVisibility(): ?string
    {
        return $this->visibility;
    }

    /**
     * Get the property name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getChildren(): array
    {
        return [$this->value];
    }
}
