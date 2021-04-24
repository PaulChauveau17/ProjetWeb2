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
        $actions = array("edit" => "edit", "remove" => "remove");
        $users = $options['data'];
        foreach($users as $user) {$userChoices[$user->getLogin()] = $user;}

        $builder
            ->add("user", ChoiceType::class, [
                'required'=> true,
                'expanded' => false,
                'multiple' => false,
                'choices' => $userChoices])
            ->add("action", ChoiceType::class, [
            'required'=> true,
            'expanded' => true,
            'multiple' => false,
            'choices' => $actions]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // 'data_class' => Users::class,
        ]);
    }
}
