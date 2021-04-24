<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChooseUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        dump($options);
        $users = $options['data'];
        $choices=[];

        foreach($users as $user) {$choices[$user->getId()] = $user->getLogin();}

        $builder
            ->add("Login", ChoiceType::class, [
            'expanded' => false,
            'required'=> true,
            'multiple' => false,
            'choices' => $choices,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
