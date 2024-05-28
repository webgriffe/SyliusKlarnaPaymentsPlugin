<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin;

use DateTimeImmutable;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\DistributionModule;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\HostedPaymentPageSession;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\Order as OrderResponse;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\OrderDetails;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\PaymentSession;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @psalm-type KlarnaOrderDetails array{id: string, fraud_status: string, redirect_url?: string, authorized_payment_method?: array{number_of_days: int, number_of_installments: int, type: string}}
 * @psalm-type KlarnaPaymentDetails array{session_id: string, client_token: string}
 * @psalm-type KlarnaHostedPaymentPageDetails array{session_id: string, session_url: string, distribution_url: string, expires_at: string, qr_code_url: string, redirect_url: string, distribution_module: array{generation_url: string, standalone_url: string, token: string}}
 * @psalm-type PaymentDetails array{payment: KlarnaPaymentDetails, hosted_payment_page?: KlarnaHostedPaymentPageDetails, order?: KlarnaOrderDetails}
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

    private const ORDER_KEY = 'order';

    private const ORDER_ID_KEY = 'id';

    private const ORDER_REDIRECT_URL_KEY = 'redirect_url';

    private const ORDER_FRAUD_STATUS_KEY = 'fraud_status';

    private const ORDER_AUTHORIZED_PAYMENT_METHOD_KEY = 'authorized_payment_method';

    private const ORDER_AUTHORIZED_PAYMENT_METHOD_NUMBER_OF_DAYS_KEY = 'number_of_days';

    private const ORDER_AUTHORIZED_PAYMENT_METHOD_NUMBER_OF_INSTALLMENTS_KEY = 'number_of_installments';

    private const ORDER_AUTHORIZED_PAYMENT_METHOD_TYPE_KEY = 'type';

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
     * @param PaymentDetails $paymentDetails
     *
     * @return PaymentDetails
     */
    public static function storeOrderOnPaymentDetails(
        array $paymentDetails,
        OrderResponse|OrderDetails $order,
    ): array {
        Assert::false(self::haveOrderData($paymentDetails));
        $paymentDetails[self::ORDER_KEY] = [
            self::ORDER_ID_KEY => $order->getOrderId(),
            self::ORDER_FRAUD_STATUS_KEY => $order->getFraudStatus(),
        ];
        if ($order instanceof OrderResponse) {
            $paymentDetails[self::ORDER_KEY][self::ORDER_REDIRECT_URL_KEY] = $order->getRedirectUrl();
            $paymentDetails[self::ORDER_KEY][self::ORDER_AUTHORIZED_PAYMENT_METHOD_KEY] = [
                self::ORDER_AUTHORIZED_PAYMENT_METHOD_NUMBER_OF_DAYS_KEY => $order->getAuthorizedPaymentMethod()->getNumberOfDays(),
                self::ORDER_AUTHORIZED_PAYMENT_METHOD_NUMBER_OF_INSTALLMENTS_KEY => $order->getAuthorizedPaymentMethod()->getNumberOfInstallments(),
                self::ORDER_AUTHORIZED_PAYMENT_METHOD_TYPE_KEY => $order->getAuthorizedPaymentMethod()->getType(),
            ];
        }

        return $paymentDetails;
    }

    /**
     * @param PaymentDetails|array $paymentDetails
     *
     * @throws InvalidArgumentException
     */
    public static function assertPaymentDetailsAreValid(array $paymentDetails): void
    {
        Assert::keyExists($paymentDetails, self::PAYMENT_KEY);
        Assert::notEmpty($paymentDetails[self::PAYMENT_KEY]);

        /** @var KlarnaPaymentDetails|array $paymentDetails */
        $paymentDetails = $paymentDetails[self::PAYMENT_KEY];
        Assert::keyExists($paymentDetails, self::PAYMENT_SESSION_ID_KEY);
        Assert::stringNotEmpty($paymentDetails[self::PAYMENT_SESSION_ID_KEY]);
        Assert::keyExists($paymentDetails, self::PAYMENT_CLIENT_TOKEN_KEY);
        Assert::stringNotEmpty($paymentDetails[self::PAYMENT_CLIENT_TOKEN_KEY]);

        if (!array_key_exists(self::HOSTED_PAYMENT_PAGE_KEY, $paymentDetails)) {
            return;
        }

        /** @var KlarnaHostedPaymentPageDetails|array $hostedPaymentPageDetails */
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

        /** @var array{generation_url: string, standalone_url: string, token: string}|array $distributionModule */
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

    /**
     * @param PaymentDetails $paymentDetails
     */
    public static function haveOrderData(array $paymentDetails): bool
    {
        if (!array_key_exists(self::ORDER_KEY, $paymentDetails)) {
            return false;
        }

        return true;
    }

    /**
     * @param PaymentDetails $paymentDetails
     */
    public static function extractHostedPaymentPageSessionFromPaymentDetails(array $paymentDetails): HostedPaymentPageSession
    {
        if (!array_key_exists(self::HOSTED_PAYMENT_PAGE_KEY, $paymentDetails)) {
            throw new \InvalidArgumentException();
        }
        $hostedPaymentPage = $paymentDetails[self::HOSTED_PAYMENT_PAGE_KEY];

        return new HostedPaymentPageSession(
            $hostedPaymentPage[self::HOSTED_PAYMENT_PAGE_SESSION_ID_KEY],
            $hostedPaymentPage[self::HOSTED_PAYMENT_PAGE_REDIRECT_URL_KEY],
            $hostedPaymentPage[self::HOSTED_PAYMENT_PAGE_SESSION_URL_KEY],
            $hostedPaymentPage[self::HOSTED_PAYMENT_PAGE_QR_CODE_URL_KEY],
            $hostedPaymentPage[self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_URL_KEY],
            new DateTimeImmutable($hostedPaymentPage[self::HOSTED_PAYMENT_PAGE_EXPIRES_AT_KEY]),
            new DistributionModule(
                $hostedPaymentPage[self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_KEY][self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_TOKEN_KEY],
                $hostedPaymentPage[self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_KEY][self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_STANDALONE_URL_KEY],
                $hostedPaymentPage[self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_KEY][self::HOSTED_PAYMENT_PAGE_DISTRIBUTION_MODULE_GENERATION_URL_KEY],
            ),
        );
    }
}
