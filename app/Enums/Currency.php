<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

class Currency extends Enum
{
    const USD = 'USD';
    const EUR = 'EUR';
    const JPY = 'JPY';
    const TWD = 'TWD';
    const CNY = 'CNY';
    const THB = 'THB';

    public function getName(): string {
        return $this->getValue();
    }
}
