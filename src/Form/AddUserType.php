<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('login', TextType::class,
                ['label' => 'login'])
            ->add('EncPwd', PasswordType::class,
                ['label' => 'password'])
            ->add('name', TextType::class,
                ['label' => 'name'])
            ->add('surname', TextType::class,
                ['label' => 'surname'])
            ->add('BirthDate', DateTimeType::class,
                ['label' => 'birthdate'])
            ->add('isAdmin', HiddenType::class,
                ['empty_data' => false]);
        // this form is going to be used by everyone -> admins shoudn't be build with this
        //dump($options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
