<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Model\Document;

/**
 * Port pour l'extraction de données depuis Owilix (Open Web Index).
 */
interface OwiDataExtractorInterface
{
    /**
     * Extrait une collection de documents depuis un dataset spécifié.
     *
     * @param string $datasetId L'identifiant du dataset à interroger.
     * @param int $limit Le nombre maximum de documents à extraire.
     *
     * @return iterable<Document>
     */
    public function extract(string $datasetId, int $limit): iterable;
}
