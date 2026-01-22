# Upgrade plugin guide

## Upgrade from version 1.x to 2.x

In this version, we have updated the plugin to be compatible with version 2 of Sylius.

- The route `@WebgriffeSyliusKlarnaPaymentsPlugin/config/shop_routing.php` has been renamed to `@WebgriffeSyliusKlarnaPaymentsPlugin/config/routes/shop.php`.
- The route `@WebgriffeSyliusKlarnaPaymentsPlugin/config/shop_ajax_routing.php` has been renamed to `@WebgriffeSyliusKlarnaPaymentsPlugin/config/routes/shop_ajax.php`.
- The page `@WebgriffeSyliusKlarnaPaymentsPlugin/Process/index.html.twig` has been replaced with `@WebgriffeSyliusKlarnaPaymentsPlugin/shop/payment/process.html.twig` and now uses twig hooks. If you have customized the previous template, please migrate your customizations to the new template using the available twig hooks.
- The asset `public/poll_payment.js` has been removed. The JS is now included in the default Webpack Encore build process.
