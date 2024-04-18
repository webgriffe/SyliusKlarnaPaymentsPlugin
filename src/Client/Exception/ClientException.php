<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\Exception;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;

final class ClientException extends Exception implements ExceptionInterface, ClientExceptionInterface
{
}
