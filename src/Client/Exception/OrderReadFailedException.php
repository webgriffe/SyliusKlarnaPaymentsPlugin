<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Exception;

use RuntimeException;

final class OrderReadFailedException extends RuntimeException implements ExceptionInterface
{
}
