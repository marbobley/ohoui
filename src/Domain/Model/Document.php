<?php

declare(strict_types=1);

namespace App\Domain\Model;

final readonly class Document
{
    public function __construct(
        private string $id,
        private string $title,
        private string $url,
        private string $content,
        private string $language,
        private string $domain,
        private ?string $highlight = null,
    ) {}

    public function getHighlight(): ?string
    {
        return $this->highlight;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }
}
