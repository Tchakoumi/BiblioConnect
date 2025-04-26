<?php

namespace App\Form;

use App\Entity\PublicationHasLanguage;
use App\Entity\Language;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class PublicationHasLanguageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('language', EntityType::class, [
                'class' => Language::class,
                'choice_label' => 'name',
                'placeholder' => 'Select a language',
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Language',
                'constraints' => [
                    new NotBlank(['message' => 'Please select a language']),
                ],
            ])
            ->add('title', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter the title in this language'
                ],
                'label' => 'Title',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a title']),
                ],
            ])
            ->add('quantity', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ],
                'label' => 'Quantity',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a quantity']),
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'Quantity should be 0 or greater'
                    ]),
                ],
            ])
            ->add('sales_price', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ],
                'label' => 'Sales Price',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a sales price']),
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'Sales price should be 0 or greater'
                    ]),
                ],
            ])
            ->add('acquisition_cost', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ],
                'label' => 'Acquisition Cost',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter an acquisition cost']),
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'Acquisition cost should be 0 or greater'
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PublicationHasLanguage::class,
        ]);
    }
}