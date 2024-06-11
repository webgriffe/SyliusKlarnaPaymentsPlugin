<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Cancel;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Webgriffe\SyliusKlarnaPlugin\Helper\PaymentDetailsHelper;
use Webgriffe\SyliusKlarnaPlugin\Model\PaymentDetails;
use Webmozart\Assert\Assert;

/**
 * @psalm-import-type StoredPaymentDetails from PaymentDetails
 */
final class CancelAction implements ActionInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param Cancel|mixed $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        Assert::isInstanceOf($request, Cancel::class);

        $payment = $request->getModel();
        Assert::isInstanceOf($payment, SyliusPaymentInterface::class);

        $this->logger->info(sprintf(
            'Start cancel action for Sylius payment with ID "%s".',
            $payment->getId(),
        ));

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
