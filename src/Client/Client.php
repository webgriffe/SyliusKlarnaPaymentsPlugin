<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client;

use GuzzleHttp\ClientInterface as GuzzleHttpClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\ServerRequest;
use const JSON_THROW_ON_ERROR;
use JsonException;
use Psr\Log\LoggerInterface;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\ServerRegion;
use Webgriffe\SyliusKlarnaPlugin\Client\Exception\ClientException;
use Webgriffe\SyliusKlarnaPlugin\Client\Exception\SessionCreateFailedException;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\ApiContext;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Payment;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\PaymentSession;

final readonly class Client implements ClientInterface
{
    public function __construct(
        private GuzzleHttpClientInterface $httpClient,
        private LoggerInterface $logger,
    ) {
    }

    public function createASession(
        ApiContext $apiContext,
        Payment $payment,
    ): PaymentSession {
        try {
            $bodyParams = json_encode($payment, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $message = 'Malformed session create request body.';
            $this->logger->error($message, ['exception' => $e]);

            throw new SessionCreateFailedException(
                $message,
                0,
                $e,
            );
        }

        $this->logger->debug('Create session request body: ' . $bodyParams);

        $request = new ServerRequest(
            'POST',
            $this->getSessionCreateUrl($apiContext),
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

            throw new SessionCreateFailedException(
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

            throw new SessionCreateFailedException(
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

    private function getSessionCreateUrl(ApiContext $apiContext): string
    {
        return sprintf('%s/payments/%s/sessions', $this->getBaseUrl($apiContext), $this->getVersion1());
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
