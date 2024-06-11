<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Model;

use Webgriffe\SyliusKlarnaPlugin\Client\Enum\PaymentSessionStatus;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\PaymentSession;

/**
 * @psalm-type KlarnaOrderDetails array{id: string, fraud_status: string, status: string}
 * @psalm-type KlarnaPaymentDetails array{session_id: string, client_token: string}
 * @psalm-type KlarnaHostedPaymentPageDetails array{session_id: string, session_url: string, distribution_url: string, expires_at: string, qr_code_url: string, redirect_url: string, distribution_module: array{generation_url: string, standalone_url: string, token: string}}
 * @psalm-type StoredPaymentDetails array{payment: KlarnaPaymentDetails, hosted_payment_page?: KlarnaHostedPaymentPageDetails, order?: KlarnaOrderDetails}
 */
final class PaymentDetails
{
    public const PAYMENT_KEY = 'payment';

    public const PAYMENT_SESSION_ID_KEY = 'session_id';

    public const PAYMENT_CLIENT_TOKEN_KEY = 'client_token';

    public const HOSTED_PAYMENT_PAGE_KEY = 'hosted_payment_page';

    public const HOSTED_PAYMENT_PAGE_SESSION_ID_KEY = 'session_id';

    public const ORDER_KEY = 'order';

    public const ORDER_ID_KEY = 'id';

    public const ORDER_FRAUD_STATUS_KEY = 'fraud_status';

    public const ORDER_STATUS_KEY = 'status';

    public const HOSTED_PAYMENT_PAGE_REDIRECT_URL_KEY = 'redirect_url';

    private ?PaymentSessionStatus $paymentSessionStatus = null;

    private ?string $hostedPaymentPageId = null;

    private ?string $hostedPaymentPageRedirectUrl = null;

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
        return $this->paymentSessionStatus;
    }

    public function setPaymentSessionStatus(?PaymentSessionStatus $paymentSessionStatus): void
    {
        $this->paymentSessionStatus = $paymentSessionStatus;
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
            $storedPaymentDetails[self::PAYMENT_KEY][self::PAYMENT_SESSION_ID_KEY],
            $storedPaymentDetails[self::PAYMENT_KEY][self::PAYMENT_CLIENT_TOKEN_KEY],
        );
        if (isset($storedPaymentDetails[self::HOSTED_PAYMENT_PAGE_KEY])) {
            $paymentDetails->setHostedPaymentPageId($storedPaymentDetails[self::HOSTED_PAYMENT_PAGE_KEY][self::HOSTED_PAYMENT_PAGE_SESSION_ID_KEY]);
            $paymentDetails->setHostedPaymentPageRedirectUrl($storedPaymentDetails[self::HOSTED_PAYMENT_PAGE_KEY][self::HOSTED_PAYMENT_PAGE_REDIRECT_URL_KEY]);
        }

        return $paymentDetails;
    }

    /**
     * @return StoredPaymentDetails
     */
    public function toStoredPaymentDetails(): array
    {
        $storedPaymentDetails = [
            self::PAYMENT_KEY => [
                self::PAYMENT_SESSION_ID_KEY => $this->getPaymentSessionId(),
                self::PAYMENT_CLIENT_TOKEN_KEY => $this->getPaymentSessionClientToken(),
            ],
        ];
        if ($this->getHostedPaymentPageId() !== null) {
            $storedPaymentDetails[self::HOSTED_PAYMENT_PAGE_KEY] = [
                self::HOSTED_PAYMENT_PAGE_SESSION_ID_KEY => $this->getHostedPaymentPageId(),
                self::HOSTED_PAYMENT_PAGE_REDIRECT_URL_KEY => $this->getHostedPaymentPageRedirectUrl(),
            ];
        }

        return $storedPaymentDetails;
    }
}
