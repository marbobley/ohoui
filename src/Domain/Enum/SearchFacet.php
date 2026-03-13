<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum SearchFacet: string
{
    case LANGUAGE = 'language';
    case DOMAIN = 'domain';

    public function label(): string
    {
        return match ($this) {
            self::LANGUAGE => 'Langue',
            self::DOMAIN => 'Domaine',
        };
    }

    public static function tryFromLabel(string $name): string|self
    {
        return self::tryFrom($name) ?? $name;
    }
}
