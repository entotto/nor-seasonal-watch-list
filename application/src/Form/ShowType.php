<?php

namespace App\Form;

use App\Entity\Season;
use App\Entity\Show;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShowType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
//            ->add('japaneseTitle')
//            ->add('englishTitle')
//            ->add('fullJapaneseTitle')
//            ->add('fullEnglishTitle')
            ->add('anilistId')
            ->add('seasons', EntityType::class, [
                'class' => Season::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->orderBy('s.rankOrder', 'ASC');
                },
                'expanded' => true,
                'multiple'=> true,
                'required' => false,
            ])
            ->add('relatedShows', EntityType::class, [
                'class' => Show::class,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $showId = $options['data'] ? $options['data']->getId() : null;
                    return $er->createQueryBuilder('sh')
                        ->andWhere('sh.id != :thisId')
                        ->setParameter('thisId', $showId)
                        ->orderBy('sh.japaneseTitle', 'ASC');
                },
                'expanded' => false,
                'multiple' => true,
                'required' => false,
            ])
            ->add('excludeFromElections', CheckboxType::class, [
                'required' => false,
                'label' => 'Exclude from elections'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Show::class,
        ]);
    }
}
