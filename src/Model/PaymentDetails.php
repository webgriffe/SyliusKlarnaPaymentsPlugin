<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Model;

use Webgriffe\SyliusKlarnaPlugin\Client\Enum\HostedPaymentPageSessionStatus;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\OrderStatus;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\PaymentSessionStatus;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\PaymentSession;

/**
 * @psalm-type StoredPaymentDetails array{payment_session_id: string, payment_client_token: string, payment_status: ?string, hosted_payment_page_session_id: ?string, hosted_payment_page_redirect_url: ?string, hosted_payment_page_status: ?string, order_id: ?string, order_status: ?string, klarna_reference: ?string}
 */
final class PaymentDetails
{
    public const PAYMENT_SESSION_ID_KEY = 'payment_session_id';

    public const PAYMENT_CLIENT_TOKEN_KEY = 'payment_client_token';

    public const PAYMENT_STATUS_KEY = 'payment_status';

    public const HOSTED_PAYMENT_PAGE_SESSION_ID_KEY = 'hosted_payment_page_session_id';

    public const HOSTED_PAYMENT_PAGE_REDIRECT_URL_KEY = 'hosted_payment_page_redirect_url';

    public const HOSTED_PAYMENT_PAGE_STATUS_KEY = 'hosted_payment_page_status';

    public const ORDER_ID_KEY = 'order_id';

    public const ORDER_STATUS_KEY = 'order_status';

    public const KLARNA_REFERENCE_KEY = 'klarna_reference';

    private ?PaymentSessionStatus $paymentStatus = null;

    private ?string $hostedPaymentPageId = null;

    private ?string $hostedPaymentPageRedirectUrl = null;

    private ?HostedPaymentPageSessionStatus $hostedPaymentPageStatus = null;

    private ?string $orderId = null;

    private ?OrderStatus $orderStatus = null;

    private ?string $klarnaReference = null;

    private function __construct(
        private readonly string $paymentSessionId,
        private readonly string $paymentSessionClientToken,
    ) {
    }

    public function getPaymentSessionId(): string
    {
        return $this->paymentSessionId;
    }

    public function getPaymentSessionClientToken(): string
    {
        return $this->paymentSessionClientToken;
    }

    public function getPaymentSessionStatus(): ?PaymentSessionStatus
    {
        return $this->paymentStatus;
    }

    public function setPaymentSessionStatus(?PaymentSessionStatus $paymentSessionStatus): void
    {
        $this->paymentStatus = $paymentSessionStatus;
    }

    public function getHostedPaymentPageId(): ?string
    {
        return $this->hostedPaymentPageId;
    }

    public function setHostedPaymentPageId(?string $hostedPaymentPageId): void
    {
        $this->hostedPaymentPageId = $hostedPaymentPageId;
    }

    public function getHostedPaymentPageRedirectUrl(): ?string
    {
        return $this->hostedPaymentPageRedirectUrl;
    }

    public function setHostedPaymentPageRedirectUrl(?string $hostedPaymentPageRedirectUrl): void
    {
        $this->hostedPaymentPageRedirectUrl = $hostedPaymentPageRedirectUrl;
    }

    public function getHostedPaymentPageStatus(): ?HostedPaymentPageSessionStatus
    {
        return $this->hostedPaymentPageStatus;
    }

    public function setHostedPaymentPageStatus(?HostedPaymentPageSessionStatus $hostedPaymentPageSessionStatus): void
    {
        $this->hostedPaymentPageStatus = $hostedPaymentPageSessionStatus;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function setOrderId(?string $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getOrderStatus(): ?OrderStatus
    {
        return $this->orderStatus;
    }

    public function setOrderStatus(?OrderStatus $orderStatus): void
    {
        $this->orderStatus = $orderStatus;
    }

    public function getKlarnaReference(): ?string
    {
        return $this->klarnaReference;
    }

    public function setKlarnaReference(?string $klarnaReference): void
    {
        $this->klarnaReference = $klarnaReference;
    }

    /**
     * @TODO
     */
    public function isCaptured(): bool
    {
        return $this->getPaymentSessionStatus() === PaymentSessionStatus::Complete;
    }

    public static function createFromPaymentSession(PaymentSession $paymentSession): self
    {
        return new self(
            $paymentSession->getSessionId(),
            $paymentSession->getClientToken(),
        );
    }

    /**
     * @param StoredPaymentDetails $storedPaymentDetails
     */
    public static function createFromStoredPaymentDetails(array $storedPaymentDetails): self
    {
        $paymentDetails = new self(
            $storedPaymentDetails[self::PAYMENT_SESSION_ID_KEY],
            $storedPaymentDetails[self::PAYMENT_CLIENT_TOKEN_KEY],
        );
        $paymentDetails->setPaymentSessionStatus($storedPaymentDetails[self::PAYMENT_STATUS_KEY] !== null ? PaymentSessionStatus::from($storedPaymentDetails[self::PAYMENT_STATUS_KEY]) : null);

        $paymentDetails->setHostedPaymentPageId($storedPaymentDetails[self::HOSTED_PAYMENT_PAGE_SESSION_ID_KEY]);
        $paymentDetails->setHostedPaymentPageRedirectUrl($storedPaymentDetails[self::HOSTED_PAYMENT_PAGE_REDIRECT_URL_KEY]);
        $paymentDetails->setHostedPaymentPageStatus($storedPaymentDetails[self::HOSTED_PAYMENT_PAGE_STATUS_KEY] !== null ? HostedPaymentPageSessionStatus::from($storedPaymentDetails[self::HOSTED_PAYMENT_PAGE_STATUS_KEY]) : null);

        $paymentDetails->setOrderId($storedPaymentDetails[self::ORDER_ID_KEY]);
        $paymentDetails->setOrderStatus($storedPaymentDetails[self::ORDER_STATUS_KEY] !== null ? OrderStatus::from($storedPaymentDetails[self::ORDER_STATUS_KEY]) : null);

        $paymentDetails->setKlarnaReference($storedPaymentDetails[self::KLARNA_REFERENCE_KEY] ?? null);

        return $paymentDetails;
    }

    /**
     * @return StoredPaymentDetails
     */
    public function toStoredPaymentDetails(): array
    {
        return [
            self::PAYMENT_SESSION_ID_KEY => $this->getPaymentSessionId(),
            self::PAYMENT_CLIENT_TOKEN_KEY => $this->getPaymentSessionClientToken(),
            self::PAYMENT_STATUS_KEY => $this->getPaymentSessionStatus()?->value,
            self::HOSTED_PAYMENT_PAGE_SESSION_ID_KEY => $this->getHostedPaymentPageId(),
            self::HOSTED_PAYMENT_PAGE_REDIRECT_URL_KEY => $this->getHostedPaymentPageRedirectUrl(),
            self::HOSTED_PAYMENT_PAGE_STATUS_KEY => $this->getHostedPaymentPageStatus()?->value,
            self::ORDER_ID_KEY => $this->getOrderId(),
            self::ORDER_STATUS_KEY => $this->getOrderStatus()?->value,
            self::KLARNA_REFERENCE_KEY => $this->getKlarnaReference(),
        ];
    }
}
