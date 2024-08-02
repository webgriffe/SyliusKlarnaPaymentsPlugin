<p align="center">
    <a href="https://sylius.com" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" />
    </a>
</p>

<h1 align="center">Sylius <a href="https://docs.klarna.com/klarna-payments/" target="_blank">Klarna</a> Plugin</h1>

<p align="center">Sylius plugin for <b>Klarna payments</b> payment method.</p>


## Installation

1. Run:
    ```bash
    composer require webgriffe/sylius-klarna-plugin
   ```

2. Add `Webgriffe\SyliusKlarnaPlugin\WebgriffeSyliusKlarnaPlugin::class => ['all' => true]` to your `config/bundles.php`.
   
   Normally, the plugin is automatically added to the `config/bundles.php` file by the `composer require` command. If it is not, you have to add it manually.

3. Create a new file config/packages/webgriffe_sylius_klarna_plugin.yaml:
   ```yaml
   imports:
       - { resource: "@WebgriffeSyliusKlarnaPlugin/config/config.php" }
    
   webgriffe_sylius_klarna:
       product_images:
       type: 'main' # Type of the product image to send to Klarna. If none is specified or the type does not exists on current product then the first image will be used.
       filter: 'sylius_medium' # Liip filter to apply to the image. If none is specified then the original image will be used.
   ```

4. Import the routes needed for cancelling the payments. Add the following to your config/routes.yaml file:
   ```yaml
   webgriffe_sylius_klarna_plugin:
       resource: "@WebgriffeSyliusKlarnaPlugin/config/shop_routing.php"
   ```
   **NB:** you should avoid to have any param prefix in your routes, otherwise the plugin won't work properly.

5. Run:
    ```bash
    php bin/console sylius:install:assets
   ```
   Or, you can add the entry to your webpack.config.js file:
    ```javascript
    .addEntry(
        'webgriffe-sylius-klarna-entry',
        './vendor/webgriffe/sylius-klarna-plugin/public/poll_payment.js'
    )
    ```
   And then override the template `WebgriffeSyliusKlarnaPlugin/Process/index.html.twig` to include the entry:
    ```twig
    {% block javascripts %}
        {{ parent() }}

        <script>
            window.afterUrl = "{{ afterUrl }}";
            window.paymentStatusUrl = "{{ paymentStatusUrl }}";
        </script>
        {{ encore_entry_script_tags('webgriffe-sylius-klarna-entry', null, 'sylius.shop') }}
    {% endblock %}
    ```

## Usage

Access to the admin panel and go to the `Payment methods` section. Create a new payment method and select `Klarna payments`
as gateway. Then, configure the payment method with the required parameters.

## Contributing

For a comprehensive guide on Sylius Plugins development please go to Sylius documentation,
there you will find the <a href="https://docs.sylius.com/en/latest/plugin-development-guide/index.html">Plugin Development Guide</a>, that is full of examples.

### Quickstart Installation

#### Traditional

1. Run `composer create-project sylius/plugin-skeleton ProjectName`.

2. From the plugin skeleton root directory, run the following commands:
   
    ```bash
    $ (cd tests/Application && yarn install)
    $ (cd tests/Application && yarn build)
    $ (cd tests/Application && APP_ENV=test bin/console assets:install public)
    
    $ (cd tests/Application && APP_ENV=test bin/console doctrine:database:create)
    $ (cd tests/Application && APP_ENV=test bin/console doctrine:schema:create)
    ```

To be able to set up a plugin's database, remember to configure you database credentials in `tests/Application/.env` and `tests/Application/.env.test`.

#### Docker

1. Execute `docker compose up -d`

2. Initialize plugin `docker compose exec app make init`

3. See your browser `open localhost`

## Usage

#### Running plugin tests

- PHPUnit
  
  ```bash
  vendor/bin/phpunit
  ```

- PHPSpec
  
  ```bash
  vendor/bin/phpspec run
  ```

- Behat (non-JS scenarios)
  
  ```bash
  vendor/bin/behat --strict --tags="~@javascript"
  ```

- Behat (JS scenarios)
    
    1. [Install Symfony CLI command](https://symfony.com/download).
    
    2. Start Headless Chrome:
  
    ```bash
    google-chrome-stable --enable-automation --disable-background-networking --no-default-browser-check --no-first-run --disable-popup-blocking --disable-default-apps --allow-insecure-localhost --disable-translate --disable-extensions --no-sandbox --enable-features=Metal --headless --remote-debugging-port=9222 --window-size=2880,1800 --proxy-server='direct://' --proxy-bypass-list='*' http://127.0.0.1
    ```
    
    3. Install SSL certificates (only once needed) and run test application's webserver on `127.0.0.1:8080`:
  
    ```bash
    symfony server:ca:install
    APP_ENV=test symfony server:start --port=8080 --dir=tests/Application/public --daemon
    ```
    
    4. Run Behat:
  
    ```bash
    vendor/bin/behat --strict --tags="@javascript"
    ```

- Static Analysis
    
    - Psalm
      
      ```bash
      vendor/bin/psalm
      ```
    
    - PHPStan
      
      ```bash
      vendor/bin/phpstan analyse -c phpstan.neon -l max src/  
      ```

- Coding Standard
  
  ```bash
  vendor/bin/ecs check
  ```

#### Opening Sylius with your plugin

- Using `test` environment:
  
    ```bash
    (cd tests/Application && APP_ENV=test bin/console sylius:fixtures:load)
    (cd tests/Application && APP_ENV=test bin/console server:run -d public)
    ```

- Using `dev` environment:
  
    ```bash
    (cd tests/Application && APP_ENV=dev bin/console sylius:fixtures:load)
    (cd tests/Application && APP_ENV=dev bin/console server:run -d public)
    ```
