<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\ServerRegion;

final class SyliusKlarnaPaymentsGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $serverRegionChoices = [];
        foreach (ServerRegion::cases() as $serverRegion) {
            $serverRegionChoices[$serverRegion->value] = $serverRegion->value;
        }

        $builder
            ->add('username', TextType::class, [
                'label' => 'webgriffe_sylius_klarna.form.gateway_configuration.username',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('password', TextType::class, [
                'label' => 'webgriffe_sylius_klarna.form.gateway_configuration.password',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('server_region', ChoiceType::class, [
                'label' => 'webgriffe_sylius_klarna.form.gateway_configuration.server_region',
                'required' => true,
                'choices' => $serverRegionChoices,
            ])
            ->add('sandbox', CheckboxType::class, [
                'label' => 'webgriffe_sylius_klarna.form.gateway_configuration.sandbox',
                'required' => false,
            ])
        ;
    }
}
