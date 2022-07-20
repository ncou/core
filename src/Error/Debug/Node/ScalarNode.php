<?php

declare(strict_types=1);

namespace Chiron\Core\Error\Debug\Node;

/**
 * Dump node for scalar values.
 */
class ScalarNode implements NodeInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string|float|int|bool|null
     */
    private $value;

    /**
     * Constructor
     *
     * @param string $type The type of scalar value.
     * @param string|float|int|bool|null $value The wrapped value.
     */
    public function __construct(string $type, $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * Get the type of value
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the value
     *
     * @return string|float|int|bool|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function getChildren(): array
    {
        return [];
    }
}
