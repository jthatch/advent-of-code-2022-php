<?php

declare(strict_types=1);

namespace App\Runner\DTO;

class CliArg
{
    public function __construct(
        public readonly string $longName,
        public readonly CliArgType $type,
        public mixed $value = null
    ) {
    }

    /**
     * Returns the argument as a "getopt" compatible string.
     */
    public function asGetOpt(): string
    {
        return $this->longName.$this->type->value;
    }

    public function setValue(mixed $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function value(): mixed
    {
        return $this->value;
    }
}
