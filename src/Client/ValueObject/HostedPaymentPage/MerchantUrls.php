<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\HostedPaymentPage;

use JsonSerializable;

final readonly class MerchantUrls implements JsonSerializable
{
    public function __construct(
        private string $back,
        private string $cancel,
        private string $error,
        private string $failure,
        private string $statusUpdate,
        private string $success,
    ) {
    }

    public function getBack(): string
    {
        return $this->back;
    }

    public function getCancel(): string
    {
        return $this->cancel;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function getFailure(): string
    {
        return $this->failure;
    }

    public function getStatusUpdate(): string
    {
        return $this->statusUpdate;
    }

    public function getSuccess(): string
    {
        return $this->success;
    }

    public function jsonSerialize(): array
    {
        return [
            'back' => $this->getBack(),
            'cancel' => $this->getCancel(),
            'error' => $this->getError(),
            'failure' => $this->getFailure(),
            'status_update' => $this->getStatusUpdate(),
            'success' => $this->getSuccess(),
        ];
    }
}
