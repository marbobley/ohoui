<?php

declare(strict_types=1);

namespace App\Service;

interface StringHandlerInterface
{
    /**
     * Extrait une valeur scalaire d'un tableau et la convertit en chaîne.
     * Si la valeur est un tableau, elle extrait le premier élément.
     *
     * @param array<array-key, mixed> $data
     */
    public function extractStringValue(array $data, string $key): string;

    /**
     * Nettoie le surlignage (highlighting) en échappant le HTML sauf pour les balises de confiance.
     */
    public function sanitizeHighlight(string $highlight, string $prefix, string $postfix): string;
}
