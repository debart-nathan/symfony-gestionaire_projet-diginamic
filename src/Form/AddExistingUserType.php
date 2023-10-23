<?php

namespace App\Form;


use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddExistingUserType extends AbstractType
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $projectId = $options['projectId'];

        $usersNotInProject = $this->userRepository->findUsersNotInProject($projectId);

        $builder
            ->add('user', ChoiceType::class, [
                'choices' => $usersNotInProject,
                'choice_label' => 'email',
                'placeholder' => 'Select a user',
                'attr' => ['class' => 'select2-enable'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'projectId' => null,
        ]);
    }
}