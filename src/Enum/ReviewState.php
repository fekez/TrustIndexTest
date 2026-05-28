<?php

declare(strict_types=1);

namespace App\Enum;

enum ReviewState: string
{
    case Published = 'published';
    case Trash = 'trash';
}
