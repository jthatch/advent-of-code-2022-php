<?php

declare(strict_types=1);

namespace App\Runner\DTO;

enum CliArgType: string
{
    case REQUIRED   = ':';
    case WITH_VALUE = '::';
    case NO_VALUE   = '';
}
