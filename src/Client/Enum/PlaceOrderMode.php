<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\Enum;

/**
 * See https://docs.klarna.com/hosted-payment-page/get-started/accept-klarna-payments-using-hosted-payment-page/#:~:text=Define%20outcome%20of%20the%20KP%20Session%20with%20Place%20Order%20Mode
 * for meaning of these values!
 */
enum PlaceOrderMode: string
{
    case PlaceOrder = 'PLACE_ORDER';
    case CaptureOrder = 'CAPTURE_ORDER';
    case None = 'NONE';
}
