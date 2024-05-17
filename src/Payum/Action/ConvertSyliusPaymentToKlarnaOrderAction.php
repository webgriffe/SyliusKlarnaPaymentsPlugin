<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Webgriffe\SyliusKlarnaPlugin\Converter\OrderConverterInterface;
use Webgriffe\SyliusKlarnaPlugin\Payum\Request\ConvertSyliusPaymentToKlarnaOrder;
use Webmozart\Assert\Assert;

final readonly class ConvertSyliusPaymentToKlarnaOrderAction implements ActionInterface
{
    public function __construct(
        private OrderConverterInterface $orderConverter,
    ) {
    }

    /**
     * @param mixed|ConvertSyliusPaymentToKlarnaOrder $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        Assert::isInstanceOf($request, ConvertSyliusPaymentToKlarnaOrder::class);

        $klarnaOrder = $this->orderConverter->convert(
            $request->getSyliusPayment(),
        );

        $request->setKlarnaOrder($klarnaOrder);
    }

    public function supports($request): bool
    {
        return $request instanceof ConvertSyliusPaymentToKlarnaOrder;
    }
}
