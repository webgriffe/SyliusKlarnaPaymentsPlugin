<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Helper;

use Webgriffe\SyliusKlarnaPaymentsPlugin\Model\PaymentDetails;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @psalm-suppress TypeDoesNotContainType
 *
 * @psalm-import-type StoredPaymentDetails from PaymentDetails
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
        Assert::keyExists($storedPaymentDetails, PaymentDetails::PAYMENT_SESSION_ID_KEY);
        Assert::stringNotEmpty($storedPaymentDetails[PaymentDetails::PAYMENT_SESSION_ID_KEY]);

        Assert::keyExists($storedPaymentDetails, PaymentDetails::PAYMENT_CLIENT_TOKEN_KEY);
        Assert::stringNotEmpty($storedPaymentDetails[PaymentDetails::PAYMENT_CLIENT_TOKEN_KEY]);

        if (array_key_exists(PaymentDetails::PAYMENT_STATUS_KEY, $storedPaymentDetails)) {
            Assert::nullOrStringNotEmpty($storedPaymentDetails[PaymentDetails::PAYMENT_STATUS_KEY]);
        }
        if (array_key_exists(PaymentDetails::HOSTED_PAYMENT_PAGE_SESSION_ID_KEY, $storedPaymentDetails)) {
            Assert::nullOrStringNotEmpty($storedPaymentDetails[PaymentDetails::HOSTED_PAYMENT_PAGE_SESSION_ID_KEY]);
        }
        if (array_key_exists(PaymentDetails::HOSTED_PAYMENT_PAGE_REDIRECT_URL_KEY, $storedPaymentDetails)) {
            Assert::nullOrStringNotEmpty($storedPaymentDetails[PaymentDetails::HOSTED_PAYMENT_PAGE_REDIRECT_URL_KEY]);
        }
        if (array_key_exists(PaymentDetails::HOSTED_PAYMENT_PAGE_STATUS_KEY, $storedPaymentDetails)) {
            Assert::nullOrStringNotEmpty($storedPaymentDetails[PaymentDetails::HOSTED_PAYMENT_PAGE_STATUS_KEY]);
        }
        if (array_key_exists(PaymentDetails::ORDER_ID_KEY, $storedPaymentDetails)) {
            Assert::nullOrStringNotEmpty($storedPaymentDetails[PaymentDetails::ORDER_ID_KEY]);
        }
        if (array_key_exists(PaymentDetails::ORDER_STATUS_KEY, $storedPaymentDetails)) {
            Assert::nullOrStringNotEmpty($storedPaymentDetails[PaymentDetails::ORDER_STATUS_KEY]);
        }
        if (array_key_exists(PaymentDetails::KLARNA_REFERENCE_KEY, $storedPaymentDetails)) {
            Assert::nullOrStringNotEmpty($storedPaymentDetails[PaymentDetails::KLARNA_REFERENCE_KEY]);
        }
    }
}
