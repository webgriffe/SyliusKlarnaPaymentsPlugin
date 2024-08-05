<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Exception;

use RuntimeException;

final class HostedPaymentPageSessionReadFailedException extends RuntimeException implements ExceptionInterface
{
}
