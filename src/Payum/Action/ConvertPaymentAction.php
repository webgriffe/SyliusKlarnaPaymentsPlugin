<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Webmozart\Assert\Assert;

final class ConvertPaymentAction implements ActionInterface
{
    /**
     * @param Convert $request
     * @return void
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface|SyliusPaymentInterface&PaymentInterface $payment */
        $payment = $request->getSource();
        Assert::isInstanceOf($payment, SyliusPaymentInterface::class);

        $order = $payment->getOrder();
        Assert::isInstanceOf($order, OrderInterface::class);

        $details = [];
        $details['acquiring_channel'] = $order->getChannel()->getCode();
        $details['intent'] = 'buy';
        $details['purchase_country'] = $order->getShippingAddress()->getCountryCode();
        $details['purchase_currency'] = $order->getCurrencyCode();
        $details['locale'] = $order->getLocaleCode();
        $details['order_amount'] = $order->getTotal();
        $details['order_tax_amount'] = $order->getTaxTotal();
        $details['order_lines'] = [];
        foreach ($order->getItems() as $item) {
            $details['order_lines'][] = [
                'type' => 'physical',
                'reference' => $item->getVariant()->getCode(),
                'name' => $item->getProductName(),
                'quantity' => $item->getQuantity(),
                'unit_price' => $item->getUnitPrice(),
                'tax_rate' => 2200,
                'total_amount' => $item->getTotal(),
                'total_discount_amount' => $item->getDiscountedUnitPrice() * $item->getQuantity(),
                'total_tax_amount' => $item->getTaxTotal(),
                'image_url' => $item->getVariant()->getProduct()->getImages()->first()->getPath(),
                'product_url' => $item->getProductName(),
            ];
        }
        $details['merchant_urls'] = [
            'authorization' => $request->getToken()->getTargetUrl(),
        ];

        $request->setResult((array) $details);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof SyliusPaymentInterface &&
            $request->getTo() == 'array'
        ;
    }
}
