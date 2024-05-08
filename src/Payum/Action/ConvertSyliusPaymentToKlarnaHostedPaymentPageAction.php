<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Webgriffe\SyliusKlarnaPlugin\Converter\HostedPaymentPageConverterInterface;
use Webgriffe\SyliusKlarnaPlugin\Payum\Request\ConvertSyliusPaymentToKlarnaHostedPaymentPage;
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
            $request->getCancelUrl(),
            $request->getPaymentSessionUrl(),
        );

        $request->setKlarnaHostedPaymentPage($klarnaHostedPaymentPage);
    }

    public function supports($request): bool
    {
        return $request instanceof ConvertSyliusPaymentToKlarnaHostedPaymentPage;
    }
}
