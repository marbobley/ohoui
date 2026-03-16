<?php

declare(strict_types=1);

namespace App\Infrastructure\Owilix;

use App\Domain\Model\Document;
use App\Domain\Repository\OwiDataExtractorInterface;
use Exception;
use Override;
use Symfony\Component\Process\Process;

use function explode;
use function trim;

/**
 * Adaptateur d'infrastructure pour extraire des données OWI via Docker et Owilix.
 */
final readonly class DockerOwiDataExtractor implements OwiDataExtractorInterface
{
    /**
     * @param string $containerName Nom du container Docker contenant l'outil 'owi'.
     */
    public function __construct(
        private string $containerName = 'sharp_feistel',
        private OwiDocumentFactory $factory = new OwiDocumentFactory(),
    ) {}

    /**
     * @return iterable<Document>
     * @throws Exception
     */
    #[Override]
    public function extract(string $datasetId, int $limit): iterable
    {
        $command = [
            'docker',
            'exec',
            $this->containerName,
            'owi',
            '--format',
            'json',
            'query',
            'less',
            '--local',
            $datasetId,
            '--limit',
            (string) $limit,
        ];

        $process = new Process($command);
        $process->setTimeout(null);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception(trim($process->getErrorOutput()));
        }

        foreach (explode("\n", trim($process->getOutput())) as $line) {
            $doc = $this->factory->fromLine($line);
            if ($doc) {
                yield $doc;
            }
        }
    }
}
