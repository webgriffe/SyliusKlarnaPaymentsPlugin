<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject;

use InvalidArgumentException;
use JsonSerializable;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\PaymentMethod;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\PlaceOrderMode;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\PurchaseType;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\HostedPaymentPage\MerchantUrls;

/**
 * @psalm-type HostedPaymentPageOptions = array{background_images?: array<array-key, array{url: string, width: string}>, logo_url?: string, page_title?: string, payment_method_categories?: PaymentMethod[], payment_method_category?: PaymentMethod, place_order_mode?: PlaceOrderMode, purchase_type?: PurchaseType, show_subtotal_detail?: 'HIDE'}
 */
final readonly class HostedPaymentPage implements JsonSerializable
{
    public const PAYMENT_METHOD_CATEGORIES_KEY = 'payment_method_categories';

    public const PAYMENT_METHOD_CATEGORY_KEY = 'payment_method_category';

    public const PLACE_ORDER_MODE_KEY = 'place_order_mode';

    public const PURCHASE_TYPE_KEY = 'purchase_type';

    private MerchantUrls $merchantUrls;

    /**
     * @param HostedPaymentPageOptions|null $options
     */
    public function __construct(
        MerchantUrls $merchantUrls,
        private string $paymentSessionUrl,
        private ?array $options = null,
        private ?string $profileId = null,
    ) {
        if ($this->options === null) {
            $this->merchantUrls = $merchantUrls;

            return;
        }
        if (array_key_exists(self::PAYMENT_METHOD_CATEGORIES_KEY, $this->options) &&
            array_key_exists(self::PAYMENT_METHOD_CATEGORY_KEY, $this->options)) {
            throw new InvalidArgumentException(sprintf(
                'You can not specify both options "%s" and "%s", you should define only one of them or neither one! See %s.',
                self::PAYMENT_METHOD_CATEGORIES_KEY,
                self::PAYMENT_METHOD_CATEGORY_KEY,
                'https://docs.klarna.com/hosted-payment-page/get-started/accept-klarna-payments-using-hosted-payment-page/#:~:text=Defining%20both%20fields%20payment_method_category%20and%20payment_method_categories%20at%20the%20same%20time%20will%20end%20up%20in%20a%20refused%20request.',
            ));
        }

        $successUrl = $merchantUrls->getSuccess();
        if (str_contains($successUrl, 'sid={{session_id}}') ||
            str_contains($successUrl, 'authorization_token={{authorization_token}}') ||
            str_contains($successUrl, 'order_id={{order_id}}')
        ) {
            $this->merchantUrls = $merchantUrls;

            return;
        }

        $hppOptions = $this->options;
        if (array_key_exists(self::PLACE_ORDER_MODE_KEY, $hppOptions) &&
            ($hppOptions[self::PLACE_ORDER_MODE_KEY] === PlaceOrderMode::PlaceOrder || $hppOptions[self::PLACE_ORDER_MODE_KEY] === PlaceOrderMode::CaptureOrder)
        ) {
            $this->merchantUrls = new MerchantUrls(
                $merchantUrls->getBack(),
                $merchantUrls->getCancel(),
                $merchantUrls->getError(),
                $merchantUrls->getFailure(),
                $merchantUrls->getStatusUpdate(),
                $successUrl . '?sid={{session_id}}&order_id={{order_id}}',
            );

            return;
        }

        $this->merchantUrls = new MerchantUrls(
            $merchantUrls->getBack(),
            $merchantUrls->getCancel(),
            $merchantUrls->getError(),
            $merchantUrls->getFailure(),
            $merchantUrls->getStatusUpdate(),
            $successUrl . '?sid={{session_id}}&authorization_token={{authorization_token}}',
        );
    }

    public function getMerchantUrls(): MerchantUrls
    {
        return $this->merchantUrls;
    }

    /**
     * @return HostedPaymentPageOptions|null
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function getPaymentSessionUrl(): string
    {
        return $this->paymentSessionUrl;
    }

    public function getProfileId(): ?string
    {
        return $this->profileId;
    }

    public function jsonSerialize(): array
    {
        $options = $this->getOptions();
        if ($options !== null) {
            if (array_key_exists(self::PAYMENT_METHOD_CATEGORIES_KEY, $options)) {
                $options[self::PAYMENT_METHOD_CATEGORIES_KEY] = array_map(static fn (PaymentMethod $paymentMethod): string => $paymentMethod->value, $options[self::PAYMENT_METHOD_CATEGORIES_KEY]);
            }
            if (array_key_exists(self::PAYMENT_METHOD_CATEGORY_KEY, $options)) {
                $options[self::PAYMENT_METHOD_CATEGORY_KEY] = $options[self::PAYMENT_METHOD_CATEGORY_KEY]->value;
            }
            if (array_key_exists(self::PLACE_ORDER_MODE_KEY, $options)) {
                $options[self::PLACE_ORDER_MODE_KEY] = $options[self::PLACE_ORDER_MODE_KEY]->value;
            }
            if (array_key_exists(self::PURCHASE_TYPE_KEY, $options)) {
                $options[self::PURCHASE_TYPE_KEY] = $options[self::PURCHASE_TYPE_KEY]->value;
            }
        }
        $payload = [
            'merchant_urls' => $this->getMerchantUrls(),
            'options' => $options,
            'payment_session_url' => $this->getPaymentSessionUrl(),
            'profile_id' => $this->getProfileId(),
        ];

        return array_filter($payload, static fn ($value) => $value !== null);
    }
}
