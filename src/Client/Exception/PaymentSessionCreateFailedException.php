<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\Exception;

use RuntimeException;

final class PaymentSessionCreateFailedException extends RuntimeException implements ExceptionInterface
{
}
