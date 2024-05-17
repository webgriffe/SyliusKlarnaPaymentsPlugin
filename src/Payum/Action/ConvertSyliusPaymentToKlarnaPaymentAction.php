<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Webgriffe\SyliusKlarnaPlugin\Converter\PaymentConverterInterface;
use Webgriffe\SyliusKlarnaPlugin\Payum\Request\ConvertSyliusPaymentToKlarnaPayment;
use Webmozart\Assert\Assert;

final readonly class ConvertSyliusPaymentToKlarnaPaymentAction implements ActionInterface
{
    public function __construct(
        private PaymentConverterInterface $paymentConverter,
    ) {
    }

    /**
     * @param ConvertSyliusPaymentToKlarnaPayment|mixed $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        Assert::isInstanceOf($request, ConvertSyliusPaymentToKlarnaPayment::class);

        $klarnaPayment = $this->paymentConverter->convert(
            $request->getSyliusPayment(),
        );

        $request->setKlarnaPayment($klarnaPayment);
    }

    public function supports($request): bool
    {
        return $request instanceof ConvertSyliusPaymentToKlarnaPayment;
    }
}
