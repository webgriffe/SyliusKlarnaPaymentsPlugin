<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client;

use DateTimeImmutable;
use GuzzleHttp\ClientInterface as GuzzleHttpClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\ServerRequest;
use const JSON_THROW_ON_ERROR;
use JsonException;
use Psr\Log\LoggerInterface;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\AcquiringChannel;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\HostedPaymentPageSessionStatus;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\Intent;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\PaymentSessionStatus;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\ServerRegion;
use Webgriffe\SyliusKlarnaPlugin\Client\Exception\ClientException;
use Webgriffe\SyliusKlarnaPlugin\Client\Exception\HostedPaymentPageSessionCreateFailedException;
use Webgriffe\SyliusKlarnaPlugin\Client\Exception\HostedPaymentPageSessionReadFailedException;
use Webgriffe\SyliusKlarnaPlugin\Client\Exception\OrderCreateFailedException;
use Webgriffe\SyliusKlarnaPlugin\Client\Exception\OrderReadFailedException;
use Webgriffe\SyliusKlarnaPlugin\Client\Exception\PaymentSessionCreateFailedException;
use Webgriffe\SyliusKlarnaPlugin\Client\Exception\PaymentSessionReadFailedException;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\ApiContext;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\HostedPaymentPage;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Order;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Payment;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\AuthorizedPaymentMethod;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\DistributionModule;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\HostedPaymentPageSession;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\HostedPaymentPageSessionDetails;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\Order as OrderResponse;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\OrderDetails;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\PaymentSession;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\PaymentSessionDetails;

final readonly class Client implements ClientInterface
{
    public function __construct(
        private GuzzleHttpClientInterface $httpClient,
        private LoggerInterface $logger,
    ) {
    }

    public function createPaymentSession(
        ApiContext $apiContext,
        Payment $payment,
    ): PaymentSession {
        try {
            $bodyParams = json_encode($payment, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $message = 'Malformed session create request body.';
            $this->logger->error($message, ['exception' => $e]);

            throw new PaymentSessionCreateFailedException(
                $message,
                0,
                $e,
            );
        }

        $this->logger->debug('Create session request body: ' . $bodyParams);

        $request = new ServerRequest(
            'POST',
            $this->getPaymentSessionCreateUrl($apiContext),
            [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . (string) $apiContext->getAuthorization(),
            ],
            $bodyParams,
        );

        try {
            $response = $this->httpClient->send($request);
        } catch (GuzzleException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            throw new ClientException($e->getMessage(), $e->getCode(), $e);
        }

        $bodyContents = $response->getBody()->getContents();
        $this->logger->debug('Create session request response: ' . $bodyContents);

        if ($response->getStatusCode() !== 200) {
            $message = sprintf(
                'Unexpected session create response status code: %s - "%s". Check merchant logs as stated here "%s"',
                $response->getStatusCode(),
                $response->getReasonPhrase(),
                'https://docs.klarna.com/klarna-payments/integrate-with-klarna-payments/step-1-initiate-a-payment/#:~:text=Here%20are%20examples%20of%20common%20errors%20with%20troubleshooting%20suggestions.%20You%20can%20use%20the%20value%20in%20correlation_id%20to%20find%20entries%20related%20to%20the%20request%20under%20Logs%20in%20the%20Merchant%20portal.',
            );
            $this->logger->error($message);

            throw new PaymentSessionCreateFailedException(
                $message,
                $response->getStatusCode(),
            );
        }

        try {
            /** @var array{client_token: string, payment_method_categories: array, session_id: string} $serializedResponse */
            $serializedResponse = json_decode(
                $bodyContents,
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $e) {
            $message = sprintf(
                'Malformed session create response body: "%s".',
                $bodyContents,
            );
            $this->logger->error($message, ['exception' => $e]);

            throw new PaymentSessionCreateFailedException(
                $message,
                $response->getStatusCode(),
                $e,
            );
        }

        return new PaymentSession(
            $serializedResponse['client_token'],
            $serializedResponse['session_id'],
        );
    }

    public function getPaymentSessionDetails(
        ApiContext $apiContext,
        string $sessionId,
    ): PaymentSessionDetails {
        $request = new ServerRequest(
            'GET',
            $this->getPaymentSessionReadUrl($apiContext, $sessionId),
            [
                'Authorization' => 'Basic ' . (string) $apiContext->getAuthorization(),
            ],
        );

        try {
            $response = $this->httpClient->send($request);
        } catch (GuzzleException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            throw new ClientException($e->getMessage(), $e->getCode(), $e);
        }

        $bodyContents = $response->getBody()->getContents();
        $this->logger->debug('Read session details request response: ' . $bodyContents);

        if ($response->getStatusCode() !== 200) {
            $message = sprintf(
                'Unexpected session details read response status code: %s - "%s".',
                $response->getStatusCode(),
                $response->getReasonPhrase(),
            );
            $this->logger->error($message);

            throw new PaymentSessionReadFailedException(
                $message,
                $response->getStatusCode(),
            );
        }

        try {
            /** @var array{acquiring_channel: string, authorization_token?: string, client_token: string, expires_at: string, status: string, intent: string} $serializedResponse */
            $serializedResponse = json_decode(
                $bodyContents,
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $e) {
            $message = sprintf(
                'Malformed session details read response body: "%s".',
                $bodyContents,
            );
            $this->logger->error($message, ['exception' => $e]);

            throw new PaymentSessionReadFailedException(
                $message,
                $response->getStatusCode(),
                $e,
            );
        }

        return new PaymentSessionDetails(
            AcquiringChannel::from($serializedResponse['acquiring_channel']),
            $serializedResponse['client_token'],
            new DateTimeImmutable($serializedResponse['expires_at']),
            PaymentSessionStatus::from($serializedResponse['status']),
            Intent::from($serializedResponse['intent']),
            $serializedResponse['authorization_token'] ?? null,
        );
    }

    public function createHostedPaymentPageSession(
        ApiContext $apiContext,
        HostedPaymentPage $hostedPaymentPage,
    ): HostedPaymentPageSession {
        try {
            $bodyParams = json_encode($hostedPaymentPage, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $message = 'Malformed hosted payment page session create request body.';
            $this->logger->error($message, ['exception' => $e]);

            throw new HostedPaymentPageSessionCreateFailedException(
                $message,
                0,
                $e,
            );
        }

        $this->logger->debug('Create hosted payment page session request body: ' . $bodyParams);

        $request = new ServerRequest(
            'POST',
            $this->getHostedPaymentPageSessionCreateUrl($apiContext),
            [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . (string) $apiContext->getAuthorization(),
            ],
            $bodyParams,
        );

        try {
            $response = $this->httpClient->send($request);
        } catch (GuzzleException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            throw new ClientException($e->getMessage(), $e->getCode(), $e);
        }

        $bodyContents = $response->getBody()->getContents();
        $this->logger->debug('Create hosted payment page session request response: ' . $bodyContents);

        if ($response->getStatusCode() !== 201) {
            $message = sprintf(
                'Unexpected hosted payment page session create response status code: %s - "%s".',
                $response->getStatusCode(),
                $response->getReasonPhrase(),
            );
            $this->logger->error($message);

            throw new HostedPaymentPageSessionCreateFailedException(
                $message,
                $response->getStatusCode(),
            );
        }

        try {
            /** @var array{distribution_module: array{generation_url: string, standalone_url: string, token: string}, distribution_url: string, expires_at: string, manual_identification_check_url: string, qr_code_url: string, redirect_url: string, session_id: string, session_url: string} $serializedResponse */
            $serializedResponse = json_decode(
                $bodyContents,
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $e) {
            $message = sprintf(
                'Malformed hosted payment page session create response body: "%s".',
                $bodyContents,
            );
            $this->logger->error($message, ['exception' => $e]);

            throw new HostedPaymentPageSessionCreateFailedException(
                $message,
                $response->getStatusCode(),
                $e,
            );
        }

        return new HostedPaymentPageSession(
            $serializedResponse['session_id'],
            $serializedResponse['redirect_url'],
            $serializedResponse['session_url'],
            $serializedResponse['qr_code_url'],
            $serializedResponse['distribution_url'],
            new DateTimeImmutable($serializedResponse['expires_at']),
            new DistributionModule(
                $serializedResponse['distribution_module']['token'],
                $serializedResponse['distribution_module']['standalone_url'],
                $serializedResponse['distribution_module']['generation_url'],
            ),
        );
    }

    public function getHostedPaymentPageSessionDetails(
        ApiContext $apiContext,
        string $sessionId,
    ): HostedPaymentPageSessionDetails {
        $request = new ServerRequest(
            'GET',
            $this->getHostedPaymentPageSessionReadUrl($apiContext, $sessionId),
            [
                'Authorization' => 'Basic ' . (string) $apiContext->getAuthorization(),
            ],
        );

        try {
            $response = $this->httpClient->send($request);
        } catch (GuzzleException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            throw new ClientException($e->getMessage(), $e->getCode(), $e);
        }

        $bodyContents = $response->getBody()->getContents();
        $this->logger->debug('Read hosted payment page session details request response: ' . $bodyContents);

        if ($response->getStatusCode() !== 200) {
            $message = sprintf(
                'Unexpected hosted payment page session details read response status code: %s - "%s".',
                $response->getStatusCode(),
                $response->getReasonPhrase(),
            );
            $this->logger->error($message);

            throw new HostedPaymentPageSessionReadFailedException(
                $message,
                $response->getStatusCode(),
            );
        }

        try {
            /** @var array{authorization_token: string, expires_at: string, klarna_reference: string, order_id: string, session_id: string, status: string, updated_at: string} $serializedResponse */
            $serializedResponse = json_decode(
                $bodyContents,
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $e) {
            $message = sprintf(
                'Malformed hosted payment page session details read response body: "%s".',
                $bodyContents,
            );
            $this->logger->error($message, ['exception' => $e]);

            throw new HostedPaymentPageSessionReadFailedException(
                $message,
                $response->getStatusCode(),
                $e,
            );
        }

        return new HostedPaymentPageSessionDetails(
            $serializedResponse['authorization_token'],
            new DateTimeImmutable($serializedResponse['expires_at']),
            $serializedResponse['klarna_reference'],
            $serializedResponse['order_id'],
            $serializedResponse['session_id'],
            HostedPaymentPageSessionStatus::from($serializedResponse['status']),
            new DateTimeImmutable($serializedResponse['updated_at']),
        );
    }

    public function createOrder(
        ApiContext $apiContext,
        Order $order,
        string $authorizationToken,
    ): OrderResponse {
        try {
            $bodyParams = json_encode($order, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $message = 'Malformed order create request body.';
            $this->logger->error($message, ['exception' => $e]);

            throw new OrderCreateFailedException(
                $message,
                0,
                $e,
            );
        }

        $this->logger->debug('Create order request body: ' . $bodyParams);

        $request = new ServerRequest(
            'POST',
            $this->getOrderCreateUrl($apiContext, $authorizationToken),
            [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . (string) $apiContext->getAuthorization(),
            ],
            $bodyParams,
        );

        try {
            $response = $this->httpClient->send($request);
        } catch (GuzzleException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            throw new ClientException($e->getMessage(), $e->getCode(), $e);
        }

        $bodyContents = $response->getBody()->getContents();
        $this->logger->debug('Create order request response: ' . $bodyContents);

        if ($response->getStatusCode() !== 200) {
            $message = sprintf(
                'Unexpected order create response status code: %s - "%s". Check logs on merchant portal for more info as stated here "%s".',
                $response->getStatusCode(),
                $response->getReasonPhrase(),
                'https://docs.klarna.com/klarna-payments/integrate-with-klarna-payments/step-3-create-an-order/#special-considerations-for-one-time-payments:~:text=one%2Dtime%20payment.-,Here%20are%20examples%20of%20common%20errors%20with%20troubleshooting%20suggestions.%20You%20can%20use%20the%20value%20in%20correlation_id%20to%20find%20entries%20related%20to%20the%20request%20under%20Logs%20in%20the%20Merchant%20portal.,-Error%20code',
            );
            $this->logger->error($message);

            throw new OrderCreateFailedException(
                $message,
                $response->getStatusCode(),
            );
        }

        try {
            /** @var array{authorized_payment_method: array{number_of_days: int, number_of_installments: int, type: string}, fraud_status: string, order_id: string, redirect_url: string} $serializedResponse */
            $serializedResponse = json_decode(
                $bodyContents,
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $e) {
            $message = sprintf(
                'Malformed order create response body: "%s".',
                $bodyContents,
            );
            $this->logger->error($message, ['exception' => $e]);

            throw new OrderCreateFailedException(
                $message,
                $response->getStatusCode(),
                $e,
            );
        }

        return new OrderResponse(
            $serializedResponse['order_id'],
            $serializedResponse['redirect_url'],
            $serializedResponse['fraud_status'],
            new AuthorizedPaymentMethod(
                $serializedResponse['authorized_payment_method']['number_of_days'],
                $serializedResponse['authorized_payment_method']['number_of_installments'],
                $serializedResponse['authorized_payment_method']['type'],
            ),
        );
    }

    public function getOrderDetails(
        ApiContext $apiContext,
        string $orderId,
    ): OrderDetails {
        $request = new ServerRequest(
            'GET',
            $this->getOrderReadUrl($apiContext, $orderId),
            [
                'Authorization' => 'Basic ' . (string) $apiContext->getAuthorization(),
            ],
        );

        try {
            $response = $this->httpClient->send($request);
        } catch (GuzzleException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            throw new ClientException($e->getMessage(), $e->getCode(), $e);
        }

        $bodyContents = $response->getBody()->getContents();
        $this->logger->debug('Read order details request response: ' . $bodyContents);

        if ($response->getStatusCode() !== 200) {
            $message = sprintf(
                'Unexpected order details read response status code: %s - "%s".',
                $response->getStatusCode(),
                $response->getReasonPhrase(),
            );
            $this->logger->error($message);

            throw new OrderReadFailedException(
                $message,
                $response->getStatusCode(),
            );
        }

        try {
            /** @var array{fraud_status: string, order_id: string} $serializedResponse */
            $serializedResponse = json_decode(
                $bodyContents,
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $e) {
            $message = sprintf(
                'Malformed order details read response body: "%s".',
                $bodyContents,
            );
            $this->logger->error($message, ['exception' => $e]);

            throw new OrderReadFailedException(
                $message,
                $response->getStatusCode(),
                $e,
            );
        }

        return new OrderDetails(
            $serializedResponse['fraud_status'],
            $serializedResponse['order_id'],
        );
    }

    public function createPaymentSessionUrl(
        ApiContext $apiContext,
        string $sessionId,
    ): string {
        return sprintf(
            '%s/payments/%s/sessions/%s',
            $this->getBaseUrl($apiContext),
            $this->getVersion1(),
            $sessionId,
        );
    }

    private function getPaymentSessionCreateUrl(ApiContext $apiContext): string
    {
        return sprintf('%s/payments/%s/sessions', $this->getBaseUrl($apiContext), $this->getVersion1());
    }

    private function getPaymentSessionReadUrl(ApiContext $apiContext, string $sessionId): string
    {
        return sprintf(
            '%s/payments/%s/sessions/%s',
            $this->getBaseUrl($apiContext),
            $this->getVersion1(),
            $sessionId,
        );
    }

    private function getHostedPaymentPageSessionCreateUrl(ApiContext $apiContext): string
    {
        return sprintf('%s/hpp/%s/sessions', $this->getBaseUrl($apiContext), $this->getVersion1());
    }

    private function getHostedPaymentPageSessionReadUrl(ApiContext $apiContext, string $sessionId): string
    {
        return sprintf(
            '%s/hpp/%s/sessions/%s',
            $this->getBaseUrl($apiContext),
            $this->getVersion1(),
            $sessionId,
        );
    }

    private function getOrderCreateUrl(ApiContext $apiContext, string $authorizationToken): string
    {
        return sprintf(
            '%s/payments/%s/authorizations/%s/order',
            $this->getBaseUrl($apiContext),
            $this->getVersion1(),
            $authorizationToken,
        );
    }

    private function getOrderReadUrl(ApiContext $apiContext, string $orderId): string
    {
        return sprintf(
            '%s/ordermanagement/%s/orders/%s',
            $this->getBaseUrl($apiContext),
            $this->getVersion1(),
            $orderId,
        );
    }

    private function getBaseUrl(ApiContext $apiContext): string
    {
        if ($apiContext->isPlayground()) {
            return match ($apiContext->getRegion()) {
                ServerRegion::Europe => Config::PLAYGROUND_EUROPE_BASE_URL,
                ServerRegion::NorthAmerica => Config::PLAYGROUND_NORTH_AMERICA_BASE_URL,
                ServerRegion::Oceania => Config::PLAYGROUND_OCEANIA_BASE_URL,
            };
        }

        return match ($apiContext->getRegion()) {
            ServerRegion::Europe => Config::PRODUCTION_EUROPE_BASE_URL,
            ServerRegion::NorthAmerica => Config::PRODUCTION_NORTH_AMERICA_BASE_URL,
            ServerRegion::Oceania => Config::PRODUCTION_OCEANIA_BASE_URL,
        };
    }

    private function getVersion1(): string
    {
        return 'v1';
    }
}
