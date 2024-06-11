<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin;

use Webgriffe\SyliusKlarnaPlugin\Model\PaymentDetails;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @psalm-import-type StoredPaymentDetails from PaymentDetails
 * @psalm-import-type KlarnaPaymentDetails from PaymentDetails
 * @psalm-import-type KlarnaHostedPaymentPageDetails from PaymentDetails
 */
final class PaymentDetailsHelper
{
    /**
     * @phpstan-assert-if-true StoredPaymentDetails $storedPaymentDetails
     */
    public static function areValid(array $storedPaymentDetails): bool
    {
        try {
            self::assertStoredPaymentDetailsAreValid($storedPaymentDetails);
        } catch (InvalidArgumentException) {
            return false;
        }

        return true;
    }

    /**
     * @phpstan-assert StoredPaymentDetails $storedPaymentDetails
     *
     * @throws InvalidArgumentException
     */
    public static function assertStoredPaymentDetailsAreValid(array $storedPaymentDetails): void
    {
        Assert::keyExists($storedPaymentDetails, PaymentDetails::PAYMENT_KEY);
        Assert::notEmpty($storedPaymentDetails[PaymentDetails::PAYMENT_KEY]);

        /** @var KlarnaPaymentDetails|array $paymentDetails */
        $paymentDetails = $storedPaymentDetails[PaymentDetails::PAYMENT_KEY];
        Assert::keyExists($paymentDetails, PaymentDetails::PAYMENT_SESSION_ID_KEY);
        Assert::stringNotEmpty($paymentDetails[PaymentDetails::PAYMENT_SESSION_ID_KEY]);
        Assert::keyExists($paymentDetails, PaymentDetails::PAYMENT_CLIENT_TOKEN_KEY);
        Assert::stringNotEmpty($paymentDetails[PaymentDetails::PAYMENT_CLIENT_TOKEN_KEY]);

        if (!array_key_exists(PaymentDetails::HOSTED_PAYMENT_PAGE_KEY, $paymentDetails)) {
            return;
        }

        /** @var KlarnaHostedPaymentPageDetails|array $hostedPaymentPageDetails */
        $hostedPaymentPageDetails = $paymentDetails[PaymentDetails::HOSTED_PAYMENT_PAGE_KEY];
        Assert::notEmpty($hostedPaymentPageDetails);
        Assert::keyExists($hostedPaymentPageDetails, PaymentDetails::HOSTED_PAYMENT_PAGE_SESSION_ID_KEY);
        Assert::stringNotEmpty($hostedPaymentPageDetails[PaymentDetails::HOSTED_PAYMENT_PAGE_SESSION_ID_KEY]);
    }
}
