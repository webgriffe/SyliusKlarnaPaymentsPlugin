<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Cancel;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Webgriffe\SyliusKlarnaPlugin\Model\PaymentDetails;
use Webgriffe\SyliusKlarnaPlugin\PaymentDetailsHelper;
use Webmozart\Assert\Assert;

/**
 * @psalm-import-type StoredPaymentDetails from PaymentDetails
 */
final class CancelAction implements ActionInterface
{
    /**
     * @param Cancel|mixed $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        Assert::isInstanceOf($request, Cancel::class);

        $payment = $request->getModel();
        Assert::isInstanceOf($payment, SyliusPaymentInterface::class);

        $paymentDetails = $payment->getDetails();
        PaymentDetailsHelper::assertStoredPaymentDetailsAreValid($paymentDetails);
    }

    public function supports($request): bool
    {
        return $request instanceof Cancel &&
            $request->getModel() instanceof SyliusPaymentInterface
        ;
    }
}
