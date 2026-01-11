<?php

namespace PhpZero\Core\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ArrayOf
{
    public function __construct(public readonly string $type)
    {
    }
}