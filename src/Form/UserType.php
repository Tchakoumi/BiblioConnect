<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'placeholder' => 'Email',
                    'class' => 'form-control',
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'User' => 'ROLE_USER',
                    'Librarian' => 'ROLE_LIBRARIAN',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('password', PasswordType::class, [
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'Password',
                    'class' => 'form-control',
                ],

            ])
            ->add('first_name', TextType::class, [
                'attr' => [
                    'placeholder' => 'First Name',
                    'class' => 'form-control',
                ],
            ])
            ->add('last_name', TextType::class, [
                'attr' => [
                    'placeholder' => 'Last Name',
                    'class' => 'form-control',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
