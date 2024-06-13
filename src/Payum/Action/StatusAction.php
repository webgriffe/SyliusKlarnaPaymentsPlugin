<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use RuntimeException;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Webgriffe\SyliusKlarnaPlugin\Helper\PaymentDetailsHelper;
use Webgriffe\SyliusKlarnaPlugin\Model\PaymentDetails;
use Webmozart\Assert\Assert;

/**
 * @psalm-import-type StoredPaymentDetails from PaymentDetails
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

        $storedPaymentDetails = $payment->getDetails();

        if ($storedPaymentDetails === []) {
            throw new RuntimeException('When are we here?');
        }

        if (!$request->isNew() && !$request->isUnknown()) {
            throw new RuntimeException('When are we here?');
        }

        if (!PaymentDetailsHelper::areValid($storedPaymentDetails)) {
            $request->markFailed();

            return;
        }
        $paymentDetails = PaymentDetails::createFromStoredPaymentDetails($storedPaymentDetails);
        if (!$paymentDetails->isCaptured()) {
            $request->markPending();

            return;
        }
        if ($paymentDetails->isSuccessfully()) {
            $request->markCaptured();

            return;
        }
        if ($paymentDetails->isFailed()) {
            $request->markFailed();

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
