<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum;

enum Currency: string
{
    case AustralianDollar = 'AUD';
    case CanadianDollar = 'CAD';
    case SwissFranc = 'CHF';
    case CzechCrown = 'CZK';
    case DanishKrone = 'DKK';
    case Euro = 'EUR';
    case BritishPoundSterling = 'GBP';
    case NorwegianKrone = 'NOK';
    case PolishZloty = 'PLN';
    case SwedishCrown = 'SEK';
    case UnitedStatesDollar = 'USD';
    case Forint = 'HUF';
    case MexicanPeso = 'MXN';
    case NewZealandDollar = 'NZD';
    case RomanianLeu = 'RON';
}
