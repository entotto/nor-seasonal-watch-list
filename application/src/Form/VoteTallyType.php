<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\View\VoteTally;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class VoteTallyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var VoteTally $voteTally */
            $voteTally = $event->getData();
            $event->getForm()->add('buffRule', TextType::class, [
                'required' => false,
                'label' => $voteTally->getShowCombinedTitle(),
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VoteTally::class,
        ]);
    }
}
