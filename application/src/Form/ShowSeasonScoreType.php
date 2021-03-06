<?php

namespace App\Form;

use App\Entity\Score;
use App\Entity\Season;
use App\Entity\Show;
use App\Entity\ShowSeasonScore;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShowSeasonScoreType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('score',  EntityType::class, [
                'class' => Score::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->orderBy('s.rankOrder', 'ASC');
                },
                'expanded' => false,
                'multiple'=> false,
                'required' => false,
                'attr' => [
                    'id' => 'show_season_score_score_' . $options['form_key']
                ]
            ]);

        if (isset($options['show_score_only'])) {
            $builder
                ->add('season', HiddenType::class, ['property_path' => 'season.id'])
                ->add('show', HiddenType::class, ['property_path' => 'show.id'])
                ->add('user', HiddenType::class, ['property_path' => 'user.id']);
            $builder->addEventListener(FormEvents::PRE_SUBMIT, function (PreSubmitEvent $event) {
                $form = $event->getForm();
                $form
                    ->remove('season')
                    ->add('season', EntityType::class,
                        ['class' => Season::class, 'multiple' => false, 'expanded' => false, 'choice_label' => 'getSeason',]
                    )
                    ->remove('show')
                    ->add('show', EntityType::class,
                        ['class' => Show::class, 'multiple' => false, 'expanded' => false, 'choice_label' => 'getShow']
                    )
                    ->remove('user')
                    ->add('user', EntityType::class,
                        ['class' => User::class, 'multiple' => false, 'expanded' => false, 'choice_label' => 'getUser']
                    );
            });
        } else {
            $builder
                ->add('season')
                ->add('show')
                ->add('user');
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ShowSeasonScore::class,
            'show_score_only' => false,
            'form_key' => 0,
        ]);
    }
}
