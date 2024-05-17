<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin;

use DateTimeImmutable;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\DistributionModule;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\HostedPaymentPageSession;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\PaymentSession;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @psalm-type KlarnaPaymentDetails array{session_id: string, client_token: string}
 * @psalm-type KlarnaHostedPaymentPageDetails array{session_id: string, session_url: string, distribution_url: string, expires_at: string, qr_code_url: string, redirect_url: string, distribution_module: array{generation_url: string, standalone_url: string, token: string}}
 * @psalm-type PaymentDetails array{payment: KlarnaPaymentDetails, hosted_payment_page?: KlarnaHostedPaymentPageDetails}
 */
final class PaymentDetailsHelper
{
    private const PAYMENT_KEY = 'payment';

    private const PAYMENT_SESSION_ID_KEY = 'session_id';

    private const PAYMENT_CLIENT_TOKEN_KEY = 'client_token';

    private const HOSTED_PAYMENT_PAGE_KEY = 'hosted_payment_page';

    private const HOSTED_PAYMENT_PAGE_SESSION_ID_KEY = 'session_id';

    private const HOSTED_PAYMENT_PAGE_SESSION_URL_KEY = 'session_url';

    private const HOSTED_PAYMENT_PAGE_DISTRIBUTION_URL_KEY = 'distribution_url';

    private const HOSTED_PAYMENT_PAGE_EXPIRES_AT_KEY = 'expires_at';

    private const HOSTED_PAYMENT_PAGE_QR_CODE_URL_KEY = 'qr_code_url';

    private const HOSTED_PAYMENT_PAGE_REDIRECT_URL_KEY = 'redirect_url';

    private const HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_KEY = 'distribution_module';

    private const HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_GENERATION_URL_KEY = 'generation_url';

    private const HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_STANDALONE_URL_KEY = 'standalone_url';

    private const HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_TOKEN_KEY = 'token';

    /**
     * @return PaymentDetails
     */
    public static function storePaymentSessionOnPaymentDetails(PaymentSession $paymentSession): array
    {
        return [
            self::PAYMENT_KEY => [
                self::PAYMENT_SESSION_ID_KEY => $paymentSession->getSessionId(),
                self::PAYMENT_CLIENT_TOKEN_KEY => $paymentSession->getClientToken(),
            ],
        ];
    }

    /**
     * @param PaymentDetails $paymentDetails
     *
     * @return PaymentDetails
     */
    public static function storeHostedPaymentPageSessionOnPaymentDetails(
        array $paymentDetails,
        HostedPaymentPageSession $hostedPaymentPageSession,
    ): array {
        Assert::false(self::haveHostedPaymentPageSessionData($paymentDetails));
        $paymentDetails[self::HOSTED_PAYMENT_PAGE_KEY] = [
            self::HOSTED_PAYMENT_PAGE_SESSION_ID_KEY => $hostedPaymentPageSession->getSessionId(),
            self::HOSTED_PAYMENT_PAGE_SESSION_URL_KEY => $hostedPaymentPageSession->getSessionUrl(),
            self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_URL_KEY => $hostedPaymentPageSession->getDistributionUrl(),
            self::HOSTED_PAYMENT_PAGE_EXPIRES_AT_KEY => $hostedPaymentPageSession->getExpiresAt()->format('Y-m-d H:i:s'),
            self::HOSTED_PAYMENT_PAGE_QR_CODE_URL_KEY => $hostedPaymentPageSession->getQrCodeUrl(),
            self::HOSTED_PAYMENT_PAGE_REDIRECT_URL_KEY => $hostedPaymentPageSession->getRedirectUrl(),
            self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_KEY => [
                self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_GENERATION_URL_KEY => $hostedPaymentPageSession->getDistributionModule()->getGenerationUrl(),
                self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_STANDALONE_URL_KEY => $hostedPaymentPageSession->getDistributionModule()->getStandaloneUrl(),
                self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_TOKEN_KEY => $hostedPaymentPageSession->getDistributionModule()->getToken(),
            ],
        ];

        return $paymentDetails;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function assertPaymentDetailsAreValid(array $paymentDetails): void
    {
        Assert::keyExists($paymentDetails, self::PAYMENT_KEY);
        Assert::notEmpty($paymentDetails[self::PAYMENT_KEY]);

        $paymentDetails = $paymentDetails[self::PAYMENT_KEY];
        Assert::keyExists($paymentDetails, self::PAYMENT_SESSION_ID_KEY);
        Assert::stringNotEmpty($paymentDetails[self::PAYMENT_SESSION_ID_KEY]);
        Assert::keyExists($paymentDetails, self::PAYMENT_CLIENT_TOKEN_KEY);
        Assert::stringNotEmpty($paymentDetails[self::PAYMENT_CLIENT_TOKEN_KEY]);

        if (!array_key_exists(self::HOSTED_PAYMENT_PAGE_KEY, $paymentDetails)) {
            return;
        }

        $hostedPaymentPageDetails = $paymentDetails[self::HOSTED_PAYMENT_PAGE_KEY];
        Assert::notEmpty($hostedPaymentPageDetails);
        Assert::keyExists($hostedPaymentPageDetails, self::HOSTED_PAYMENT_PAGE_SESSION_ID_KEY);
        Assert::stringNotEmpty($hostedPaymentPageDetails[self::HOSTED_PAYMENT_PAGE_SESSION_ID_KEY]);
        Assert::keyExists($hostedPaymentPageDetails, self::HOSTED_PAYMENT_PAGE_SESSION_URL_KEY);
        Assert::stringNotEmpty($hostedPaymentPageDetails[self::HOSTED_PAYMENT_PAGE_SESSION_URL_KEY]);
        Assert::keyExists($hostedPaymentPageDetails, self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_URL_KEY);
        Assert::stringNotEmpty($hostedPaymentPageDetails[self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_URL_KEY]);
        Assert::keyExists($hostedPaymentPageDetails, self::HOSTED_PAYMENT_PAGE_EXPIRES_AT_KEY);
        Assert::stringNotEmpty($hostedPaymentPageDetails[self::HOSTED_PAYMENT_PAGE_EXPIRES_AT_KEY]);
        Assert::keyExists($hostedPaymentPageDetails, self::HOSTED_PAYMENT_PAGE_QR_CODE_URL_KEY);
        Assert::stringNotEmpty($hostedPaymentPageDetails[self::HOSTED_PAYMENT_PAGE_QR_CODE_URL_KEY]);
        Assert::keyExists($hostedPaymentPageDetails, self::HOSTED_PAYMENT_PAGE_REDIRECT_URL_KEY);
        Assert::stringNotEmpty($hostedPaymentPageDetails[self::HOSTED_PAYMENT_PAGE_REDIRECT_URL_KEY]);
        Assert::keyExists($hostedPaymentPageDetails, self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_KEY);
        Assert::notEmpty($hostedPaymentPageDetails[self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_KEY]);

        $distributionModule = $hostedPaymentPageDetails[self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_KEY];
        Assert::keyExists($distributionModule, self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_GENERATION_URL_KEY);
        Assert::stringNotEmpty($distributionModule[self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_GENERATION_URL_KEY]);
        Assert::keyExists($distributionModule, self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_STANDALONE_URL_KEY);
        Assert::stringNotEmpty($distributionModule[self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_STANDALONE_URL_KEY]);
        Assert::keyExists($distributionModule, self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_TOKEN_KEY);
        Assert::stringNotEmpty($distributionModule[self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_TOKEN_KEY]);
    }

    /**
     * @param PaymentDetails $paymentDetails
     */
    public static function extractPaymentSessionFromPaymentDetails(array $paymentDetails): PaymentSession
    {
        return new PaymentSession(
            $paymentDetails[self::PAYMENT_KEY][self::PAYMENT_CLIENT_TOKEN_KEY],
            $paymentDetails[self::PAYMENT_KEY][self::PAYMENT_SESSION_ID_KEY],
        );
    }

    /**
     * @param PaymentDetails $paymentDetails
     */
    public static function haveHostedPaymentPageSessionData(array $paymentDetails): bool
    {
        if (!array_key_exists(self::HOSTED_PAYMENT_PAGE_KEY, $paymentDetails)) {
            return false;
        }

        return true;
    }

    public static function extractHostedPaymentPageSessionFromPaymentDetails(array $paymentDetails): HostedPaymentPageSession
    {
        return new HostedPaymentPageSession(
            $paymentDetails[self::HOSTED_PAYMENT_PAGE_KEY][self::HOSTED_PAYMENT_PAGE_SESSION_ID_KEY],
            $paymentDetails[self::HOSTED_PAYMENT_PAGE_KEY][self::HOSTED_PAYMENT_PAGE_REDIRECT_URL_KEY],
            $paymentDetails[self::HOSTED_PAYMENT_PAGE_KEY][self::HOSTED_PAYMENT_PAGE_SESSION_URL_KEY],
            $paymentDetails[self::HOSTED_PAYMENT_PAGE_KEY][self::HOSTED_PAYMENT_PAGE_QR_CODE_URL_KEY],
            $paymentDetails[self::HOSTED_PAYMENT_PAGE_KEY][self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_URL_KEY],
            new DateTimeImmutable($paymentDetails[self::HOSTED_PAYMENT_PAGE_KEY][self::HOSTED_PAYMENT_PAGE_EXPIRES_AT_KEY]),
            new DistributionModule(
                $paymentDetails[self::HOSTED_PAYMENT_PAGE_KEY][self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_KEY][self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_TOKEN_KEY],
                $paymentDetails[self::HOSTED_PAYMENT_PAGE_KEY][self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_KEY][self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_STANDALONE_URL_KEY],
                $paymentDetails[self::HOSTED_PAYMENT_PAGE_KEY][self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_KEY][self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_GENERATION_URL_KEY],
            ),
        );
    }
}
