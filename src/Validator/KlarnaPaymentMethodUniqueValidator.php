<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Validator;

use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Webgriffe\SyliusKlarnaPlugin\Payum\KlarnaPaymentsApi;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class KlarnaPaymentMethodUniqueValidator extends ConstraintValidator
{
    public function __construct(
        private readonly PaymentMethodRepositoryInterface $paymentMethodRepository,
    ) {
    }

    /**
     * @param mixed|PaymentMethodInterface $value
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof PaymentMethodInterface) {
            throw new UnexpectedValueException($value, PaymentMethodInterface::class);
        }

        if (!$constraint instanceof KlarnaPaymentMethodUnique) {
            throw new UnexpectedValueException($constraint, KlarnaPaymentMethodUnique::class);
        }

        $gatewayConfig = $value->getGatewayConfig();
        /** @psalm-suppress DeprecatedMethod */
        if ($gatewayConfig === null || $gatewayConfig->getFactoryName() !== KlarnaPaymentsApi::CODE) {
            return;
        }

        /** @var PaymentMethodInterface[] $paymentMethods */
        $paymentMethods = $this->paymentMethodRepository->findAll();
        /** @psalm-suppress DeprecatedMethod */
        $paymentMethodsWithSameGatewayConfig = array_filter(
            $paymentMethods,
            static fn (PaymentMethodInterface $paymentMethod) => $paymentMethod->getGatewayConfig()?->getFactoryName() === $gatewayConfig->getFactoryName(),
        );
        if (count($paymentMethodsWithSameGatewayConfig) > 1 ||
            (count($paymentMethodsWithSameGatewayConfig) === 1 && reset($paymentMethodsWithSameGatewayConfig) !== $value)
        ) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('gatewayConfig')
                ->addViolation()
            ;
        }
    }
}
