<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class SyliusKlarnaPaymentsGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
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
            ->add('sandbox', CheckboxType::class, [
                'label' => 'webgriffe_sylius_klarna.form.gateway_configuration.sandbox',
                'required' => false,
            ])
        ;
    }
}
