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
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\ServerRegion;
use Webgriffe\SyliusKlarnaPlugin\Client\Exception\ClientException;
use Webgriffe\SyliusKlarnaPlugin\Client\Exception\HostedPaymentPageSessionCreateFailedException;
use Webgriffe\SyliusKlarnaPlugin\Client\Exception\PaymentSessionCreateFailedException;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\ApiContext;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\HostedPaymentPage;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Payment;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\DistributionModule;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\HostedPaymentPageSession;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\PaymentSession;

final readonly class Client implements ClientInterface
{
    public function __construct(
        private GuzzleHttpClientInterface $httpClient,
        private LoggerInterface $logger,
    ) {
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
                'Unexpected session create response status code: %s - "%s".',
                $response->getStatusCode(),
                $response->getReasonPhrase(),
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

    private function getPaymentSessionCreateUrl(ApiContext $apiContext): string
    {
        return sprintf('%s/payments/%s/sessions', $this->getBaseUrl($apiContext), $this->getVersion1());
    }

    private function getHostedPaymentPageSessionCreateUrl(ApiContext $apiContext): string
    {
        return sprintf('%s/hpp/%s/sessions', $this->getBaseUrl($apiContext), $this->getVersion1());
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
