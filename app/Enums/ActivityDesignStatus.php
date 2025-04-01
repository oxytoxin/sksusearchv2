<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ActivityDesignStatus: int implements HasLabel
{
    case DRAFT = 0;
    case IN_APPROVAL = 1;
    case APPROVED = 10;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::IN_APPROVAL => 'In Approval',
            self::APPROVED => 'Approved',
        };
    }
}
