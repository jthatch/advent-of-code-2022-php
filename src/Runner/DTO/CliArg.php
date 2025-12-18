<?php

declare(strict_types=1);

namespace App\Runner\DTO;

readonly class CliArg
{
    public function __construct(
        public string $longName,
        public string $shortName,
        public CliArgType $type,
        public mixed $value = null
    ) {
    }

    // returns the argument as a "getopt" compatible string
    public function asGetOpt(): string
    {
        return $this->longName.$this->type->value;
    }

    // create a new instance with updated value
    public function withValue(mixed $value): self
    {
        return new self($this->longName, $this->shortName, $this->type, $value);
    }
}
