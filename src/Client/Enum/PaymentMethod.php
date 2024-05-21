<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\Enum;

/**
 * See section on url https://docs.klarna.com/hosted-payment-page/get-started/accept-klarna-payments-using-hosted-payment-page/#:~:text=Payment%20Methods%20and%20Categories
 * to understand meaning of these values
 */
enum PaymentMethod: string
{
    case DirectDebit = 'DIRECT_DEBIT';
    case DirectBankTransfer = 'DIRECT_BANK_TRANSFER';
    case PayNow = 'PAY_NOW';
    case PayLater = 'PAY_LATER';
    case PayOverTime = 'PAY_OVER_TIME';
    case Klarna = 'KLARNA';
}
