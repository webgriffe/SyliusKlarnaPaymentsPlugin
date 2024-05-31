<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject;

use JsonSerializable;

final readonly class Attachment implements JsonSerializable
{
    public function __construct(
        private string $body,
        private string $contentType,
    ) {
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function jsonSerialize(): array
    {
        return [
            'body' => $this->getBody(),
            'content_type' => $this->getContentType(),
        ];
    }
}
