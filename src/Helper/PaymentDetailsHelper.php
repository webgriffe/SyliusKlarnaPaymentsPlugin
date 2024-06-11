<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Helper;

use Webgriffe\SyliusKlarnaPlugin\Model\PaymentDetails;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

/**
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
     * @TODO Da fare ancora
     *
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
    }
}
