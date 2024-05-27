<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

class Language extends Enum
{
    const ENGLISH = 'en';
    const SPANISH = 'es';
    const JAPANESE = 'jp';
    const CHINESE_TRADITIONAL_TAIWAN = 'zh-tw';
    const CHINESE_SIMPLIFIED = 'zh-cn';
    const THAI = 'th';

    public function getName(): string {
        return match ($this->value) {
            self::ENGLISH => 'English',
            self::SPANISH => 'Spanish',
            self::JAPANESE => 'Japanese',
            self::CHINESE_TRADITIONAL_TAIWAN => 'Chinese (Taiwan)',
            self::CHINESE_SIMPLIFIED => 'Chinese (Simplified)',
            self::THAI => 'Thai',
            default => 'Unknown',
        };
    }
}
