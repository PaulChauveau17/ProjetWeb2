<?php

namespace App\Form;

// use App\Entity\Carts;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShopCartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'];
        $items = $data['items'];
        $users = $data['users'];

        foreach($users as $user) {$userChoices[$user->getLogin()] = $user;}
        foreach($items as $item) {
            $stock = $item->getStock();
            if ($stock > 0) {
                $description = $item->getDescription();
                $price = $item->getPrice();
                $stock = $item->getStock();
                $itemChoices["$description ($$price) ($stock in stock)"] = $item;
            }
        }
        $quantityChoices = array("1" => 1, "10" => 10, "100" => 100);
        // TODO: find a clever solution


        $builder->add("user", ChoiceType::class, [
            'required'=> true,
            'expanded' => false,
            'multiple' => false,
            'choices' => $userChoices]);
        $builder->add("item", ChoiceType::class, [
            'required'=> true,
            'expanded' => false,
            'multiple' => false,
            'choices' => $itemChoices]);
        $builder->add("quantity", ChoiceType::class, [
            'required'=> true,
            'expanded' => false,
            'multiple' => false,
            'choices' => $quantityChoices]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // 'data_class' => Carts::class,
        ]);
    }
}
