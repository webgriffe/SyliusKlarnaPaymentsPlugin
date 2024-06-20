<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusKlarnaPlugin\Behat\Context\Ui;

use Behat\Behat\Context\Context;
use Behat\Mink\Session;
use Sylius\Behat\Page\Shop\Order\ShowPageInterface;
use Sylius\Behat\Page\Shop\Order\ThankYouPageInterface;
use Sylius\Bundle\PayumBundle\Model\PaymentSecurityTokenInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Tests\Webgriffe\SyliusKlarnaPlugin\Behat\Context\PayumPaymentTrait;
use Tests\Webgriffe\SyliusKlarnaPlugin\Behat\Page\Shop\Payment\ProcessPageInterface;
use Webmozart\Assert\Assert;

final class KlarnaContext implements Context
{
    use PayumPaymentTrait;

    /**
     * @param RepositoryInterface<PaymentSecurityTokenInterface> $paymentTokenRepository
     * @param PaymentRepositoryInterface<PaymentInterface> $paymentRepository
     * @param OrderRepositoryInterface<OrderInterface> $orderRepository
     */
    public function __construct(
        private readonly RepositoryInterface $paymentTokenRepository,
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Session $session,
        private readonly ProcessPageInterface $paymentProcessPage,
        private readonly ThankYouPageInterface $thankYouPage,
        private readonly ShowPageInterface $orderShowPage,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ShowPageInterface $orderDetails,
    ) {
        // TODO: Why config parameters are not loaded?
        $this->urlGenerator->setContext(new RequestContext('', 'GET', '127.0.0.1:8080', 'https'));
    }

    /**
     * @When I complete the payment on Klarna
     */
    public function iCompleteThePaymentOnKlarna(): void
    {
        $payment = $this->getCurrentPayment();
        [$paymentCaptureSecurityToken] = $this->getCurrentPaymentSecurityTokens($payment);

        // Simulate coming back from Klarna after completed checkout
        $this->session->getDriver()->visit($paymentCaptureSecurityToken->getTargetUrl());
    }

    /**
     * @Given I have cancelled Klarna payment
     * @When I cancel the payment on Klarna
     */
    public function iCancelThePaymentOnKlarna(): void
    {
        $payment = $this->getCurrentPayment();
        [$paymentCaptureSecurityToken, $paymentNotifySecurityToken, $paymentCancelSecurityToken] = $this->getCurrentPaymentSecurityTokens($payment);

        // Simulate coming back from Klarna after clicking on cancel link
        $this->session->getDriver()->visit($paymentCancelSecurityToken->getTargetUrl());
    }

    /**
     * @Then I should be on the waiting payment processing page
     */
    public function iShouldBeOnTheWaitingPaymentProcessingPage(): void
    {
        $payment = $this->getCurrentPayment();
        $this->paymentProcessPage->verify([
            'tokenValue' => $payment->getOrder()->getTokenValue(),
        ]);
    }

    /**
     * @Then /^I should be redirected to the thank you page$/
     */
    public function iShouldBeRedirectedToTheThankYouPage(): void
    {
        $this->paymentProcessPage->waitForRedirect();
        Assert::true($this->thankYouPage->hasThankYouMessage());
    }

    /**
     * @When I try to pay again with Klarna
     */
    public function iTryToPayAgainWithKlarna(): void
    {
        $this->orderShowPage->pay();
        $this->iCompleteThePaymentOnKlarna();
    }

    /**
     * @Then /^I should be redirected to the order page page$/
     */
    public function iShouldBeRedirectedToTheOrderPagePage(): void
    {
        $this->paymentProcessPage->waitForRedirect();
        $orders = $this->orderRepository->findAll();
        $order = reset($orders);
        Assert::isInstanceOf($order, OrderInterface::class);
        Assert::true($this->orderShowPage->isOpen(['tokenValue' => $order->getTokenValue()]));
    }

    /**
     * @Then I should be notified that my payment is failed
     */
    public function iShouldBeNotifiedThatMyPaymentHasBeenCancelled(): void
    {
        $this->assertNotification('Payment has failed.');
    }

    /**
     * @return PaymentRepositoryInterface<PaymentInterface>
     */
    protected function getPaymentRepository(): PaymentRepositoryInterface
    {
        return $this->paymentRepository;
    }

    /**
     * @return RepositoryInterface<PaymentSecurityTokenInterface>
     */
    protected function getPaymentTokenRepository(): RepositoryInterface
    {
        return $this->paymentTokenRepository;
    }

    private function assertNotification(string $expectedNotification): void
    {
        $notifications = $this->orderDetails->getNotifications();
        $hasNotifications = '';

        foreach ($notifications as $notification) {
            $hasNotifications .= $notification;
            if ($notification === $expectedNotification) {
                return;
            }
        }

        throw new \RuntimeException(sprintf('There is no notification with "%s". Got "%s"', $expectedNotification, $hasNotifications));
    }
}
