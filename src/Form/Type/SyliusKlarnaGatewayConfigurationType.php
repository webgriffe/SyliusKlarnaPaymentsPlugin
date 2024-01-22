<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class SyliusKlarnaGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('merchant_id', TextType::class, [
                'required' => true,
            ])
            ->add('secret', TextType::class, [
                'required' => true,
            ])
            ->add('contentType', TextType::class, [
                'required' => false,
            ])
            ->add('termsUri', TextType::class, [
                'required' => false,
            ])
            ->add('checkoutUri', TextType::class, [
                'required' => false,
            ])
            ->add('sandbox', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }
}
