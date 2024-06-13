<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Cancel;
use Payum\Core\Security\TokenInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Webgriffe\SyliusKlarnaPlugin\Controller\PaymentController;
use Webgriffe\SyliusKlarnaPlugin\Helper\PaymentDetailsHelper;
use Webgriffe\SyliusKlarnaPlugin\Model\PaymentDetails;
use Webmozart\Assert\Assert;

/**
 * @psalm-suppress TypeDoesNotContainType
 *
 * @psalm-import-type StoredPaymentDetails from PaymentDetails
 */
final class CancelAction implements ActionInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly RequestStack $requestStack,
        private readonly RouterInterface $router,
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

        /** @var string|int $paymentId */
        $paymentId = $payment->getId();

        $this->logger->info(sprintf(
            'Start cancel action for Sylius payment with ID "%s".',
            $paymentId,
        ));

        $paymentDetails = $payment->getDetails();
        PaymentDetailsHelper::assertStoredPaymentDetailsAreValid($paymentDetails);

        $this->logger->info('Redirecting the user to the Sylius Klarna Payments waiting page.');

        $session = $this->requestStack->getSession();
        $session->set(PaymentController::PAYMENT_ID_SESSION_KEY, $paymentId);
        $cancelToken = $request->getToken();
        Assert::isInstanceOf($cancelToken, TokenInterface::class);
        $session->set(PaymentController::TOKEN_HASH_SESSION_KEY, $cancelToken->getHash());

        $order = $payment->getOrder();
        Assert::isInstanceOf($order, OrderInterface::class);

        throw new HttpRedirect(
            $this->router->generate('webgriffe_sylius_klarna_plugin.payment.process', [
                'tokenValue' => $order->getTokenValue(),
            ]),
        );
    }

    public function supports($request): bool
    {
        return $request instanceof Cancel &&
            $request->getModel() instanceof SyliusPaymentInterface
        ;
    }
}
