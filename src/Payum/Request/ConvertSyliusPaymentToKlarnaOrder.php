<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\Request;

use Sylius\Component\Core\Model\PaymentInterface;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Order;

final class ConvertSyliusPaymentToKlarnaOrder
{
    private ?Order $klarnaOrder = null;

    public function __construct(
        private readonly PaymentInterface $syliusPayment,
    ) {
    }

    public function getSyliusPayment(): PaymentInterface
    {
        return $this->syliusPayment;
    }

    public function setKlarnaOrder(Order $klarnaOrder): void
    {
        $this->klarnaOrder = $klarnaOrder;
    }

    public function getKlarnaOrder(): ?Order
    {
        return $this->klarnaOrder;
    }
}
