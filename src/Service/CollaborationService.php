<?php

namespace App\Service;

use App\Entity\Collaboration;
use App\Entity\User;
use App\Repository\CollaborationRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class CollaborationService
{


    public function __construct(
        private EntityManagerInterface $entityManager,
        private CollaborationRepository $collaborationRepository,
        private ProjectRepository $projectRepository,
        private Security $security,
        private AuthService $authService
    ) {
 
    }

    public function manageCollaboration($projectId)
    {
        $project = $this->projectRepository->find($projectId);
        if ($this->collaborationRepository->findAdminUserByProject($project) != $this->security->getUser()) {
            return null;
        }

        $collaborations = $this->collaborationRepository->findBy(['project' => $projectId]);

        return [
            'project' => $project,
            'collaborations' => $collaborations,
        ];
    }

    public function addExistingUser($user, $projectId)
    {
        $project = $this->projectRepository->find($projectId);

        $existingCollaboration = $this->collaborationRepository->findOneBy([
            'user' => $user,
            'project' => $project,
        ]);

        if ($existingCollaboration !== null) {
            return null;
        }

        $collaboration = new Collaboration();
        $collaboration->setUser($user);
        $collaboration->setProject($project);
        $collaboration->setIsAdmin(false);

        $this->entityManager->persist($collaboration);
        $this->entityManager->flush();

        return $collaboration;
    }

    public function addNewUser($form, $projectId)
    {
        $project = $this->projectRepository->find($projectId);

        $user = $this->authService->registerUser($form);

        $collaboration = new Collaboration();
        $user->addCollaboration($collaboration);
        $project->addCollaboration($collaboration);
        $collaboration->setIsAdmin(false);

        $this->entityManager->persist($collaboration);
        $this->entityManager->flush();

        return $collaboration;
    }

    public function deleteCollaboration($collaborationId)
    {
        $collaboration = $this->collaborationRepository->find($collaborationId);
        if ($collaboration && !$collaboration->getIsAdmin()) {
            $this->entityManager->remove($collaboration);
            $this->entityManager->flush();
        }

        return $collaboration;
    }
}
