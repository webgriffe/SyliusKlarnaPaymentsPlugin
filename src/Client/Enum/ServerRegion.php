<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum;

enum ServerRegion: string
{
    case Europe = 'europe';
    case NorthAmerica = 'north_america';
    case Oceania = 'oceania';
}
