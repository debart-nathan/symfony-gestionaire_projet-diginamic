<?php

namespace App\Controller;

use App\Entity\Collaboration;
use App\Form\RegistrationFormType;
use App\Form\AddExistingUserType;
use App\Repository\CollaborationRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/project')]
class CollaborationController extends AbstractController
{
    #[Route('/{projectId}/collaborations', name: 'manage_collaboration')]
    public function manageCollaboration(
        ProjectRepository $projectRepository,
        CollaborationRepository $collaborationRepository,
        $projectId
    ): Response {
        $project = $projectRepository->find($projectId);
        if ($collaborationRepository->findAdminUserByProject($project) != $this->getUser()) {
            // Redirect to project list or show an error message
            return $this->redirectToRoute('list_project');
        }

        $formAddExistingUser = $this->createForm(AddExistingUserType::class, null, [
            'projectId' => $projectId,
        ]);
        $formAddNewUser = $this->createForm(RegistrationFormType::class);

        $collaborations = $collaborationRepository->findBy(['project' => $projectId]);

        return $this->render('collaboration/manage.html.twig', [
            'project' => $project,
            'collaborations' => $collaborations,
            'formAddExistingUser' => $formAddExistingUser->createView(),
            'formAddNewUser' => $formAddNewUser->createView(),
        ]);
    }

    #[Route('/{projectId}/collaborations/add-existing', name: 'add_existing_user')]
    public function addExistingUser(
        Request $request,
        EntityManagerInterface $entityManager,
        CollaborationRepository $collaborationRepository,
        ProjectRepository $projectRepository,
        $projectId
    ): Response {

        $formAddExistingUser = $this->createForm(AddExistingUserType::class, null, [
            'projectId' => $projectId,
        ]);
        $formAddExistingUser->handleRequest($request);
        if ($formAddExistingUser->isSubmitted() && $formAddExistingUser->isValid()) {
            // Add existing user to project as collaborator
            $user = $formAddExistingUser->getData()['user'];
            $project = $projectRepository->find($projectId);

            $existingCollaboration = $collaborationRepository->findOneBy([
                'user' => $user,
                'project' => $project,
            ]);

            if ($existingCollaboration !== null) {
                // Collaboration already exists, redirect or show error message
                return $this->redirectToRoute('manage_collaboration', ['projectId' => $projectId]);
            }

            $collaboration = new Collaboration();
            $collaboration->setUser($user);
            $collaboration->setProject($projectRepository->find($projectId));
            $collaboration->setIsAdmin(false);

            $entityManager->persist($collaboration);
            $entityManager->flush();
        }
        return $this->redirectToRoute('manage_collaboration', ['projectId' => $projectId]);
    }

    #[Route('{projectId}/collaborations/add-new', name: 'add_new_user')]
    public function addNewUser(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordEncoder,
        ProjectRepository $projectRepository,
        $projectId
    ): Response {
        $formAddNewUser = $this->createForm(RegistrationFormType::class);
        $formAddNewUser->handleRequest($request);
        $project = $projectRepository->find($projectId);
        if ($formAddNewUser->isSubmitted() && $formAddNewUser->isValid()) {
            $user = $formAddNewUser->getData();
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->hashPassword(
                    $user,
                    $formAddNewUser->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            $collaboration = new Collaboration();
            $collaboration->setUser($user);
            $collaboration->setProject($project);
            $collaboration->setIsAdmin(false);

            $entityManager->persist($collaboration);
            $entityManager->flush();

            return $this->redirectToRoute('manage_collaboration', ['projectId' => $projectId]);
        }
        return $this->redirectToRoute('manage_collaboration', ['projectId' => $projectId]);
    }

    #[Route('/{projectId}/collaborations/{collaborationId}/delete', name: 'delete_collaboration')]
    public function deleteCollaboration(
        EntityManagerInterface $entityManager,
        CollaborationRepository $collaborationRepository,
        $projectId,
        $collaborationId
    ): Response {
        $collaboration = $collaborationRepository->find($collaborationId);
        if ($collaboration && !$collaboration->getIsAdmin()) {
            $entityManager->remove($collaboration);
            $entityManager->flush();
        }
        return $this->redirectToRoute('manage_collaboration', ['projectId' => $projectId]);
    }
}
