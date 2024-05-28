<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\Exception;

use RuntimeException;

final class OrderReadFailedException extends RuntimeException implements ExceptionInterface
{
}
