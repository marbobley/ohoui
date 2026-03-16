<?php

declare(strict_types=1);

namespace App\Infrastructure\Owilix;

use App\Domain\Model\Document;
use App\Infrastructure\Owilix\Exception\OwiInvalidJsonException;
use App\Infrastructure\Owilix\Exception\OwiLineSkippedException;
use App\Infrastructure\Owilix\Exception\OwilixException;
use App\Infrastructure\Owilix\Exception\OwiMissingUrlException;

use function is_array;
use function json_decode;
use function md5;
use function parse_url;
use function trim;

use const PHP_URL_HOST;

/**
 * @internal
 */
final readonly class OwiDocumentFactory
{
    private const string SUCCESS = '✅ Processing completed successfully with no errors!';

    /**
     * @throws OwilixException
     */
    public function fromLine(string $line): ?Document
    {
        $line = $this->assertSkippedLine($line);
        $data = $this->assertIsArrayThenReturnData($line);
        $url = $this->assertUrlIsNotEmptyThenReturnUrl($data['url'], $line);
        return $this->createDocument($data, $url);
    }

    private function shouldSkipLine(string $line): bool
    {
        return $line === '' || $line === self::SUCCESS;
    }

    /**
     * @param array<string, mixed> $data
     * @param string               $url
     * @return Document
     */
    private function createDocument(array $data, string $url): Document
    {
        $domain = (string) ($data['url_domain'] ?? parse_url($url, PHP_URL_HOST));

        return new Document(
            id: (string) ($data['id'] ?? md5($url)),
            title: (string) ($data['title'] ?? 'Sans titre'),
            url: $url,
            content: (string) ($data['main_content'] ?? ''),
            language: (string) ($data['language'] ?? 'unknown'),
            domain: $domain === '' ? 'unknown' : $domain,
        );
    }

    /**
     * @throws OwiLineSkippedException
     */
    public function assertSkippedLine(string $line): string
    {
        $line = trim($line);
        if ($this->shouldSkipLine($line)) {
            throw OwiLineSkippedException::because($line);
        }

        return $line;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws OwiInvalidJsonException
     */
    public function assertIsArrayThenReturnData(string $line): array
    {
        /** @var mixed $data */
        $data = json_decode($line, associative: true);
        if (!is_array($data)) {
            throw OwiInvalidJsonException::forLine($line);
        }

        /** @var array<string, mixed> $data */
        return $data;
    }

    /**
     * @param mixed  $url1
     * @param string $line
     *
     * @return string
     * @throws OwiMissingUrlException
     */
    public function assertUrlIsNotEmptyThenReturnUrl(mixed $url1, string $line): string
    {
        $url = (string) ($url1 ?? '');
        if ($url === '') {
            throw OwiMissingUrlException::inLine($line);
        }

        return $url;
    }
}
