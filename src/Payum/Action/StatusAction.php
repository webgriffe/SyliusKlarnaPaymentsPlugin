<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
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
            $request->markNew();

            return;
        }

        if (!$request->isNew() && !$request->isUnknown()) {
            // Payment status already set
            return;
        }

        PaymentDetailsHelper::assertPaymentDetailsAreValid($paymentDetails);

        /** @psalm-suppress InvalidArgument */
        $paymentStatus = PaymentDetailsHelper::getPaymentStatus($paymentDetails);

        if (in_array($paymentStatus, [PaymentState::CANCELLED, PaymentState::PENDING], true)) {
            $request->markCanceled();

            return;
        }

        if (in_array($paymentStatus, [PaymentState::SUCCESS, PaymentState::AWAITING_CONFIRMATION], true)) {
            $request->markCaptured();

            return;
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
