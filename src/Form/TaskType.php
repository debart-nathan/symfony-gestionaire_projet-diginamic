<?php

namespace App\Form;

use App\Entity\Task;
use App\Entity\Collaboration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['userIsAdmin']) {
            $builder->add('state', ChoiceType::class, [
                'choices' => [
                    'En attente' => 'en attente',
                    'En cours' => 'en cours',
                    'Terminé' => 'terminé',
                    'Archivé' => 'archivé',
                ],
            ]);
        } else {
            $builder
                ->add('name', null)
                ->add('description', null)
                ->add('state', ChoiceType::class, [
                    'choices' => [
                        'En attente' => 'en attente',
                        'En cours' => 'en cours',
                        'Terminé' => 'terminé',
                        'Archivé' => 'archivé',
                    ],
                ])
                ->add('collaboration', EntityType::class, [
                    'class' => Collaboration::class,
                    'choices' => $options['data']->getProject()->getCollaborations(),
                    'choice_label' => function (Collaboration $collaboration) {
                        return $collaboration->getUser()->getEmail();
                    },
                    'placeholder' => 'No collaborator',
                    'required' =>false,
                    'attr' => ['class' => 'select2-enable'],
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
            'userIsAdmin' => null,
        ]);
    }
}
