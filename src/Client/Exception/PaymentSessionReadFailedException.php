<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\Exception;

use RuntimeException;

final class PaymentSessionReadFailedException extends RuntimeException implements ExceptionInterface
{
}
