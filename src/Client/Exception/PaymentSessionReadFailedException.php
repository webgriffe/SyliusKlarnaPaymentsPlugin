<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Exception;

use RuntimeException;

final class PaymentSessionReadFailedException extends RuntimeException implements ExceptionInterface
{
}
