<?php

namespace App\Enums;

use ArchTech\Enums\Values;

enum TimeEntryTypeEnum: string
{
    use Values;

    case ESTIMATE = 'estimate';
    case SPENT = 'spent';
}