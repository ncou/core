<?php

declare(strict_types=1);

namespace Chiron\Core\Error\Debug\Node;

/**
 * Debug node for special messages like errors or recursion warnings.
 */
class SpecialNode implements NodeInterface
{
    /**
     * @var string
     */
    private $value;

    /**
     * Constructor
     *
     * @param string $value The message/value to include in dump results.
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Get the message/value
     *
     * @return string
     */
    public function getValue(): string
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
