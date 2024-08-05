<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Response;

final readonly class Order
{
    public function __construct(
        private string $orderId,
        private string $redirectUrl,
        private string $fraudStatus,
        private AuthorizedPaymentMethod $authorizedPaymentMethod,
    ) {
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }

    public function getFraudStatus(): string
    {
        return $this->fraudStatus;
    }

    public function getAuthorizedPaymentMethod(): AuthorizedPaymentMethod
    {
        return $this->authorizedPaymentMethod;
    }
}
