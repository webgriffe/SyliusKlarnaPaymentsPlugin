<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusKlarnaPlugin\Behat\Context\Api;

use Behat\Behat\Context\Context;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Sylius\Bundle\PayumBundle\Model\PaymentSecurityTokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Tests\Webgriffe\SyliusKlarnaPlugin\Behat\Context\PayumPaymentTrait;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\HostedPaymentPageSessionStatus;

final class KlarnaContext implements Context
{
    use PayumPaymentTrait;

    /**
     * @param RepositoryInterface<PaymentSecurityTokenInterface> $paymentTokenRepository
     * @param PaymentRepositoryInterface<PaymentInterface> $paymentRepository
     */
    public function __construct(
        private readonly RepositoryInterface $paymentTokenRepository,
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ClientInterface|Client $client,
    ) {
        // TODO: Why config parameters are not loaded?
        $this->urlGenerator->setContext(new RequestContext('', 'GET', '127.0.0.1:8080', 'https'));
    }

    /**
     * @When Klarna notify the store about the successful payment
     */
    public function klarnaNotifyTheStoreAboutTheSuccessfulPayment(): void
    {
        $payment = $this->getCurrentPayment();
        [$paymentCaptureSecurityToken, $paymentNotifySecurityToken] = $this->getCurrentPaymentSecurityTokens($payment);

        $this->notifyPaymentState($paymentNotifySecurityToken, [
            'event_id' => random_bytes(5),
            'session' => [
                'session_id' => random_bytes(5),
                'status' => HostedPaymentPageSessionStatus::Completed->value,
                'updated_at' => (new DateTime())->format('Y-m-d H:i:s'),
                'expires_at' => (new DateTime('+1 day'))->format('Y-m-d H:i:s'),
                'order_id' => '123456',
                'klarna_reference' => 'KLARNA-123456',
            ],
        ]);
    }

    /**
     * @When /^Klarna notify the store about the failed payment$/
     */
    public function klarnaNotifyTheStoreAboutTheFailedPayment(): void
    {
        $payment = $this->getCurrentPayment();
        [$paymentCaptureSecurityToken, $paymentNotifySecurityToken] = $this->getCurrentPaymentSecurityTokens($payment);

        $this->notifyPaymentState($paymentNotifySecurityToken, [
            'event_id' => random_bytes(5),
            'session' => [
                'session_id' => random_bytes(5),
                'status' => HostedPaymentPageSessionStatus::Failed->value,
                'updated_at' => (new DateTime())->format('Y-m-d H:i:s'),
                'expires_at' => (new DateTime('+1 day'))->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * @When /^Klarna notify the store about the cancelled payment$/
     */
    public function klarnaNotifyTheStoreAboutTheCancelledPayment(): void
    {
        $payment = $this->getCurrentPayment();
        [$paymentCaptureSecurityToken, $paymentNotifySecurityToken] = $this->getCurrentPaymentSecurityTokens($payment);

        $this->notifyPaymentState($paymentNotifySecurityToken, [
            'event_id' => random_bytes(5),
            'session' => [
                'session_id' => random_bytes(5),
                'status' => HostedPaymentPageSessionStatus::Cancelled->value,
                'updated_at' => (new DateTime())->format('Y-m-d H:i:s'),
                'expires_at' => (new DateTime('+1 day'))->format('Y-m-d H:i:s'),
            ],
        ]);
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

    private function notifyPaymentState(PaymentSecurityTokenInterface $token, array $responsePayload): void
    {
        $formParams = http_build_query($responsePayload);
        $request = new Request(
            'POST',
            $this->getNotifyUrl($token),
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            $formParams,
        );
        if ($this->client instanceof Client) {
            $this->client->send($request);

            return;
        }
        $this->client->sendRequest($request);
    }

    private function getNotifyUrl(PaymentSecurityTokenInterface $token): string
    {
        return $this->urlGenerator->generate(
            'payum_notify_do',
            ['payum_token' => $token->getHash()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
