<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChooseCartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $actions = array("edit" => "edit", "remove" => "remove");
        $users = $options['data']['users'];
        $carts = $options['data']['carts'];
        $items = $options['data']['items'];

        foreach($carts as $cart) {
            $str = "Owned by ";
            foreach ($users as $user) {
                if ($user->getID() == $cart->getUserID()) {
                    $str .= $user->getLogin();
                }
            }
            $str .= " which contains ";
            foreach ($items as $item) {
                if ($item->getID() == $cart->getItemID()) {
                    $str .= $item->getDescription();
                }
            }
            $quantity = $cart->getQuantity();
            $str .= " (x$quantity)";
            $cartChoices[$str] = $cart;

        }

        $builder
            ->add("cart", ChoiceType::class, [
                'required'=> true,
                'expanded' => false,
                'multiple' => false,
                'choices' => $cartChoices])
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
