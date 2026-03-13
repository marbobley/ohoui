<?php

declare(strict_types=1);

namespace App\Application\Service;

interface SolrHealthUseCaseInterface
{
    /**
     * @return array<string, mixed>
     */
    public function execute(): array;
}
