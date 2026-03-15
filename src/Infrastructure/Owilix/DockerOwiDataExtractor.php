<?php

declare(strict_types=1);

namespace App\Infrastructure\Owilix;

use App\Domain\Model\Document;
use App\Domain\Repository\OwiDataExtractorInterface;
use Exception;
use Override;
use Symfony\Component\Process\Process;

use function explode;
use function is_array;
use function is_string;
use function json_decode;
use function md5;
use function parse_url;
use function trim;

use const PHP_URL_HOST;

/**
 * Adaptateur d'infrastructure pour extraire des données OWI via Docker et Owilix.
 */
final readonly class DockerOwiDataExtractor implements OwiDataExtractorInterface
{
    /** @var string */
    private const SUCCESS_MESSAGE = '✅ Processing completed successfully with no errors!';

    /**
     * @param string $containerName Nom du container Docker contenant l'outil 'owi'.
     */
    public function __construct(
        private string $containerName = 'sharp_feistel',
    ) {}

    /**
     * @return iterable<Document>
     */
    #[Override]
    public function extract(string $datasetId, int $limit): iterable
    {
        $lines = $this->getRawLines($datasetId, $limit);

        foreach ($lines as $line) {
            $document = $this->parseLine($line);
            if ($document !== null) {
                yield $document;
            }
        }
    }

    /**
     * @return array<string>
     */
    private function getRawLines(string $datasetId, int $limit): array
    {
        $command = [
            'docker', 'exec', $this->containerName,
            'owi', '--format', 'json', 'query', 'less',
            '--local', $datasetId,
            '--limit', (string) $limit,
        ];

        $process = new Process($command);
        $process->setTimeout(null);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception(trim($process->getErrorOutput()));
        }

        return explode("\n", trim($process->getOutput()));
    }

    private function parseLine(string $line): ?Document
    {
        $trimmedLine = trim($line);

        if ($trimmedLine === '' || $trimmedLine === self::SUCCESS_MESSAGE) {
            return null;
        }

        $data = json_decode($trimmedLine, associative: true, depth: 512);

        if (!is_array($data) || !is_string($data['url'] ?? null)) {
            return null;
        }

        $id = $data['id'] ?? md5($data['url']);
        $domain = $this->resolveDomain($data);

        return new Document(
            id: $id,
            title: $data['title'] ?? 'Sans titre',
            url: $data['url'],
            content: $data['main_content'] ?? '',
            language: $data['language'] ?? 'unknown',
            domain: $domain
        );
    }

    private function resolveDomain(array $data): string
    {
        $domain = $data['url_domain'] ?? (parse_url($data['url'], PHP_URL_HOST) ?? 'unknown');

        return $domain === '' ? 'unknown' : $domain;
    }
}
