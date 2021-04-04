<?php

namespace App\Form;

use App\Entity\UserPreferences;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserPreferencesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) {
                    $form = $event->getForm();
                    /** @var UserPreferences $data */
                    $data = $event->getData();
                    $form->add('colors_mode_picker', ChoiceType::class, [
                        'mapped' => false,
                        'choices' => [
                            'Operating system (does not work in all browsers)' => 'os',
                            'Light mode' => 'light',
                            'Dark mode' => 'dark',
                        ],
                        'multiple' => false,
                        'expanded' => true,
                        'required' => true,
                        'label' => 'Color mode',
                        'data' => $data->getColorsMode(),
                    ]);
                }
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserPreferences::class,
        ]);
    }
}
