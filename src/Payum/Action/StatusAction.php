<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use RuntimeException;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\OrderStatus;
use Webgriffe\SyliusKlarnaPlugin\PaymentDetailsHelper;
use Webmozart\Assert\Assert;

/**
 * @psalm-import-type PaymentDetails from \Webgriffe\SyliusKlarnaPlugin\PaymentDetailsHelper
 */
final class StatusAction implements ActionInterface
{
    /**
     * @param GetStatus|mixed $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        Assert::isInstanceOf($request, GetStatus::class);

        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getFirstModel();

        /** @var array{}|PaymentDetails $paymentDetails */
        $paymentDetails = $payment->getDetails();

        if ($paymentDetails === []) {
            throw new RuntimeException('When are we here?');
        }

        if (!$request->isNew() && !$request->isUnknown()) {
            throw new RuntimeException('When are we here?');
        }

        PaymentDetailsHelper::assertPaymentDetailsAreValid($paymentDetails);
        /** @var PaymentDetails $paymentDetails */
        $paymentDetails = $paymentDetails;
        $orderDetails = PaymentDetailsHelper::extractOrderFromPaymentDetails($paymentDetails);

        if ($orderDetails->getStatus() === OrderStatus::Captured) {
            $request->markCaptured();

            return;
        }
        if ($orderDetails->getStatus() === OrderStatus::Cancelled) {
            $request->markCanceled();

            return;
        }
        if ($orderDetails->getStatus() === OrderStatus::Expired) {
            $request->markExpired();

            return;
        }
        if ($orderDetails->getStatus() === OrderStatus::Closed) {
            throw new RuntimeException('What is closed status?');
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof GetStatus &&
            $request->getFirstModel() instanceof SyliusPaymentInterface
        ;
    }
}
