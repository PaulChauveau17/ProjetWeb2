<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChooseItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $actions = array("edit" => "edit", "remove" => "remove");
        $items = $options['data'];
        foreach($items as $item) {$itemChoices[$item->getDescription()] = $item;}

        $builder
            ->add("item", ChoiceType::class, [
                'required'=> true,
                'expanded' => false,
                'multiple' => false,
                'choices' => $itemChoices])
            ->add("action", ChoiceType::class, [
            'required'=> true,
            'expanded' => true,
            'multiple' => false,
            'choices' => $actions]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // 'data_class' => items::class,
        ]);
    }
}
