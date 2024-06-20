<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Psr\Log\LoggerInterface;
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
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param GetStatus|mixed $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        Assert::isInstanceOf($request, GetStatus::class);

        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getFirstModel();

        $this->logger->info(sprintf(
            'Start status action for Sylius payment with ID "%s".',
            (string) $payment->getId(),
        ));

        $storedPaymentDetails = $payment->getDetails();

        if ($storedPaymentDetails === []) {
            $this->logger->info('Empty stored details.');

            throw new RuntimeException('When are we here?');
        }

        if (!$request->isNew() && !$request->isUnknown()) {
            $this->logger->info('Request new or unknown.', ['isNew' => $request->isNew(), 'isUnknown' => $request->isUnknown()]);

            throw new RuntimeException('When are we here?');
        }

        if (!PaymentDetailsHelper::areValid($storedPaymentDetails)) {
            $this->logger->info('Payment details not valid. Payment marked as failed');
            $request->markFailed();

            return;
        }
        $paymentDetails = PaymentDetails::createFromStoredPaymentDetails($storedPaymentDetails);
        if (!$paymentDetails->isCaptured()) {
            $this->logger->info('Payment not already captured. Payment marked as pending.');
            $request->markPending();

            return;
        }
        if ($paymentDetails->isSuccessfully()) {
            $this->logger->info('Payment successfully. Payment marked as captured.');
            $request->markCaptured();

            return;
        }
        if ($paymentDetails->isFailed()) {
            $this->logger->info('Payment failed. Payment marked as failed.');
            $request->markFailed();

            return;
        }
        if ($paymentDetails->isCanceled()) {
            $this->logger->info('Payment canceled. Payment marked as canceled.');
            $request->markCanceled();

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
