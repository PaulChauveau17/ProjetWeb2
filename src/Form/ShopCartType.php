<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShopCartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$actions = array("add" => "add", "remove" => "remove", "show" => "show");
        $data = $options['data'];
        $items = $data['items'];
        $users = $data['users'];

        foreach($users as $user) {$userChoices[$user->getLogin()] = $user;}
        foreach($items as $item) {
            if ($item->getStock() > 0)
            $itemChoices[$item->getDescription()] = $item;
        }

        $builder
            ->add("user", ChoiceType::class, [
                'required'=> true,
                'expanded' => true,
                'multiple' => false,
                'choices' => $userChoices])
            /* TODO: ajouter le prix de chaque item */
            ->add("item", ChoiceType::class, [
                'required'=> true,
                'expanded' => true,
                'multiple' => false,
                'choices' => $itemChoices])
            /* TODO: changer la quantité de l'item en fonction du nombre en stock */
            ->add("quantity", HiddenType::class,
            ['empty_data' => 1]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // 'data_class' => carts::class,
        ]);
    }
}
