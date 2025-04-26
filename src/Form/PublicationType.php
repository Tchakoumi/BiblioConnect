<?php

namespace App\Form;

use App\Entity\Publication;
use App\Entity\Theme;
use App\Entity\Author;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PublicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('theme', EntityType::class, [
                'class' => Theme::class,
                'choice_label' => 'title',
                'placeholder' => 'Select a theme',
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Theme'
            ])
            ->add('author', EntityType::class, [
                'class' => Author::class,
                'choice_label' => 'fullName',
                'placeholder' => 'Select an author',
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Author'
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'Select a category',
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Category'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Publication::class,
        ]);
    }
}