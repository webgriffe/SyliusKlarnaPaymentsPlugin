<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\DependencyInjection;

use Sylius\Bundle\CoreBundle\DependencyInjection\PrependDoctrineMigrationsTrait;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Webmozart\Assert\Assert;

final class WebgriffeSyliusKlarnaPaymentsExtension extends AbstractResourceExtension implements PrependExtensionInterface
{
    use PrependDoctrineMigrationsTrait;

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration([], $container);
        Assert::notNull($configuration);
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $loader->load('services.php');

        $this->addImageOptionsOnConverters($container, $config);
    }

    public function prepend(ContainerBuilder $container): void
    {
        $this->prependDoctrineMigrations($container);
    }

    protected function getMigrationsNamespace(): string
    {
        return 'DoctrineMigrations';
    }

    protected function getMigrationsDirectory(): string
    {
        return '@WebgriffeSyliusKlarnaPaymentsPlugin/migrations';
    }

    protected function getNamespacesOfMigrationsExecutedBefore(): array
    {
        return [
            'Sylius\Bundle\CoreBundle\Migrations',
        ];
    }

    private function addImageOptionsOnConverters(ContainerBuilder $container, array $config): void
    {
        $definition = $container->getDefinition('webgriffe_sylius_klarna_payments.converter.order');
        $definition->setArgument('$mainImageType', $config['product_images']['type']);
        $definition->setArgument('$imageFilter', $config['product_images']['filter']);

        $definition = $container->getDefinition('webgriffe_sylius_klarna_payments.converter.payment');
        $definition->setArgument('$mainImageType', $config['product_images']['type']);
        $definition->setArgument('$imageFilter', $config['product_images']['filter']);
    }
}
