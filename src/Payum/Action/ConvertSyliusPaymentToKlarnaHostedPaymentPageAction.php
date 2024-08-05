<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Converter\HostedPaymentPageConverterInterface;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\Request\ConvertSyliusPaymentToKlarnaHostedPaymentPage;
use Webmozart\Assert\Assert;

final readonly class ConvertSyliusPaymentToKlarnaHostedPaymentPageAction implements ActionInterface
{
    public function __construct(
        private HostedPaymentPageConverterInterface $hostedPaymentPageConverter,
    ) {
    }

    /**
     * @param ConvertSyliusPaymentToKlarnaHostedPaymentPage|mixed $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        Assert::isInstanceOf($request, ConvertSyliusPaymentToKlarnaHostedPaymentPage::class);

        $klarnaHostedPaymentPage = $this->hostedPaymentPageConverter->convert(
            $request->getConfirmationUrl(),
            $request->getNotificationUrl(),
            $request->getBackUrl(),
            $request->getCancelUrl(),
            $request->getErrorUrl(),
            $request->getFailureUrl(),
            $request->getPaymentSessionUrl(),
        );

        $request->setKlarnaHostedPaymentPage($klarnaHostedPaymentPage);
    }

    public function supports($request): bool
    {
        return $request instanceof ConvertSyliusPaymentToKlarnaHostedPaymentPage;
    }
}
