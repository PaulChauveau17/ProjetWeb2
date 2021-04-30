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
        dump($options);
        $carts = $options['data'];

        foreach($carts as $cart) {
            $str = "Owned by ";
            $str .= $cart->getUser()->getLogin();
            $str .= " which contains ";
            $str .= $cart->getItem()->getDescription();
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
            //'data_class' => Carts::class,
        ]);
    }
}
