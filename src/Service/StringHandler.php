<?php

declare(strict_types=1);

namespace App\Service;

use Override;

use function htmlspecialchars;
use function is_array;
use function reset;
use function str_replace;

use const ENT_HTML5;
use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

readonly class StringHandler implements StringHandlerInterface
{
    #[Override]
    public function extractStringValue(array $data, string $key): string
    {
        /** @var mixed $value */
        $value = $data[$key] ?? '';

        return is_array($value) ? (string) reset($value) : (string) $value;
    }

    #[Override]
    public function sanitizeHighlight(string $highlight, string $prefix, string $postfix): string
    {
        // On échappe tout le HTML de manière sécurisée
        $sanitized = htmlspecialchars($highlight, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, encoding: 'UTF-8');

        // On ré-autorise uniquement les balises EXACTES générées
        return str_replace(
            [
                htmlspecialchars($prefix, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, encoding: 'UTF-8'),
                htmlspecialchars($postfix, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, encoding: 'UTF-8'),
            ],
            [
                $prefix,
                $postfix,
            ],
            $sanitized,
        );
    }
}
