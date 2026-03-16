<?php

declare(strict_types=1);

namespace App\Infrastructure\Owilix;

use App\Domain\Model\Document;

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

    public function fromLine(string $line): ?Document
    {

        $line = trim($line);
        if ($line === '' || $line === self::SUCCESS) {
            return null;
        }

        $data = json_decode($line, associative: true);
        if (!is_array($data)) {
            return null;
        }

        $url = (string) ($data['url'] ?? '');
        if ($url === '') {
            return null;
        }

        $domain = (string) ($data['url_domain'] ?? parse_url($url, PHP_URL_HOST));

        return new Document(
            (string) ($data['id'] ?? md5($url)),
            (string) ($data['title'] ?? 'Sans titre'),
            $url,
            (string) ($data['main_content'] ?? ''),
            (string) ($data['language'] ?? 'unknown'),
            $domain === '' ? 'unknown' : $domain,
        );
    }
}
