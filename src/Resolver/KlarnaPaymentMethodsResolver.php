<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Resolver;

use InvalidArgumentException;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface as BasePaymentInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApi;
use Webmozart\Assert\Assert;

final readonly class KlarnaPaymentMethodsResolver implements PaymentMethodsResolverInterface
{
    public function __construct(
        private PaymentMethodRepositoryInterface $paymentMethodRepository,
        private PaymentCountryResolverInterface $paymentCountryResolver,
    ) {
    }

    /**
     * @param BasePaymentInterface|PaymentInterface $subject
     *
     * @return PaymentMethodInterface[]
     */
    public function getSupportedMethods(BasePaymentInterface $subject): array
    {
        Assert::true($this->supports($subject), 'This payment is not support by current resolver');
        Assert::isInstanceOf($subject, PaymentInterface::class);

        $order = $subject->getOrder();
        Assert::isInstanceOf($order, OrderInterface::class);

        $channel = $order->getChannel();
        Assert::isInstanceOf($channel, ChannelInterface::class);

        /** @var PaymentMethodInterface[] $paymentMethods */
        $paymentMethods = $this->paymentMethodRepository->findEnabledForChannel($channel);

        $currencyCode = $order->getCurrencyCode();
        Assert::string($currencyCode);

        return array_filter(
            $paymentMethods,
            function (PaymentMethodInterface $paymentMethod) use ($subject, $currencyCode) {
                $gatewayConfig = $paymentMethod->getGatewayConfig();
                if ($gatewayConfig === null) {
                    return false;
                }
                /** @psalm-suppress DeprecatedMethod */
                if ($gatewayConfig->getFactoryName() !== KlarnaPaymentsApi::CODE) {
                    return true;
                }

                try {
                    $paymentCountry = $this->paymentCountryResolver->resolve($subject);
                } catch (InvalidArgumentException) {
                    return false;
                }

                return $paymentCountry->getCurrency()->value === $currencyCode;
            },
        );
    }

    public function supports(BasePaymentInterface $subject): bool
    {
        if (!$subject instanceof PaymentInterface) {
            return false;
        }
        $order = $subject->getOrder();
        if (!$order instanceof OrderInterface) {
            return false;
        }
        $channel = $order->getChannel();
        if (!$channel instanceof ChannelInterface) {
            return false;
        }
        $paymentMethod = $subject->getMethod();
        if (!$paymentMethod instanceof PaymentMethodInterface) {
            return false;
        }
        $billingAddress = $order->getBillingAddress();
        if (!$billingAddress instanceof AddressInterface) {
            return false;
        }
        $currencyCode = $order->getCurrencyCode();

        return $currencyCode !== null;
    }
}
