<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin;

use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\HostedPaymentPageSession;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\PaymentSession;

/**
 * @psalm-type KlarnaPaymentDetails array{session_id: string, client_token: string}
 * @psalm-type KlarnaHostedPaymentPageDetails array{session_id: string, session_url: string, distribution_url: string, expires_at: string, qr_code_url: string, redirect_url: string, distribution_module: array{generation_url: string, standalone_url: string, token: string}}
 * @psalm-type PaymentDetails array{payment: KlarnaPaymentDetails, hosted_payment_page: KlarnaHostedPaymentPageDetails}
 */
final class PaymentDetailsHelper
{
    /**
     * @return PaymentDetails
     */
    public static function createFromContractCreateResult(
        PaymentSession $paymentSession,
        HostedPaymentPageSession $hostedPaymentPageSession,
    ): array {
        return [
            'payment' => [
                'session_id' => $paymentSession->getSessionId(),
                'client_token' => $paymentSession->getClientToken(),
            ],
            'hosted_payment_page' => [
                'session_id' => $hostedPaymentPageSession->getSessionId(),
                'session_url' => $hostedPaymentPageSession->getSessionUrl(),
                'distribution_url' => $hostedPaymentPageSession->getDistributionUrl(),
                'expires_at' => $hostedPaymentPageSession->getExpiresAt()->format('Y-m-d H:i:s'),
                'qr_code_url' => $hostedPaymentPageSession->getQrCodeUrl(),
                'redirect_url' => $hostedPaymentPageSession->getRedirectUrl(),
                'distribution_module' => [
                    'generation_url' => $hostedPaymentPageSession->getDistributionModule()->getGenerationUrl(),
                    'standalone_url' => $hostedPaymentPageSession->getDistributionModule()->getStandaloneUrl(),
                    'token' => $hostedPaymentPageSession->getDistributionModule()->getToken(),
                ],
            ],
        ];
    }
}
