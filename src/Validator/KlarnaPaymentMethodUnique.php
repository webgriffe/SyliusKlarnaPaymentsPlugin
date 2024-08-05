<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class KlarnaPaymentMethodUnique extends Constraint
{
    public string $message = 'webgriffe_sylius_klarna.payment_method.unique';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
