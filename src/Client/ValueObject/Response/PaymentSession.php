<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Response;

final readonly class PaymentSession
{
    public function __construct(
        private string $clientToken,
        private string $sessionId,
    ) {
    }

    public function getClientToken(): string
    {
        return $this->clientToken;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }
}
