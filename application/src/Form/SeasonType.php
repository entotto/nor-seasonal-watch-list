<?php

namespace App\Form;

use App\Entity\Season;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeasonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('year', NumberType::class, [
                'required' => false,
            ])
            ->add('yearPart', TextType::class, [
                'required' => false,
            ])
            ->add('rankOrder', NumberType::class)
            ->add('hiddenFromSeasonsList', CheckboxType::class, [
                'required' => false,
                'label' => 'Watch/Rewatch'
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Season::class,
        ]);
    }
}
