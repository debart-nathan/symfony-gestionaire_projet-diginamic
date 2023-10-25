<?php

namespace App\Controller;

use App\Form\RegistrationFormType;
use App\Form\AddExistingUserType;
use App\Service\CollaborationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/project')]
class CollaborationController extends AbstractController
{
    #[Route('/{projectId}/collaborations', name: 'manage_collaboration')]
    public function manageCollaboration(
        CollaborationService $collaborationService,
        $projectId
    ): Response {
        $data = $collaborationService->manageCollaboration($projectId);
        if ($data === null) {
            return $this->redirectToRoute('list_project');
        }

        $formAddExistingUser = $this->createForm(AddExistingUserType::class, null, [
            'projectId' => $projectId,
        ]);
        $formAddNewUser = $this->createForm(RegistrationFormType::class);

        return $this->render('collaboration/manage.html.twig', [
            'project' => $data['project'],
            'collaborations' => $data['collaborations'],
            'formAddExistingUser' => $formAddExistingUser->createView(),
            'formAddNewUser' => $formAddNewUser->createView(),
        ]);
    }

    #[Route('/{projectId}/collaborations/add-existing', name: 'add_existing_user')]
    public function addExistingUser(
        Request $request,
        CollaborationService $collaborationService,
        $projectId
    ): Response {
        $formAddExistingUser = $this->createForm(AddExistingUserType::class, null, [
            'projectId' => $projectId,
        ]);
        $formAddExistingUser->handleRequest($request);
        if ($formAddExistingUser->isSubmitted() && $formAddExistingUser->isValid()) {
            $collaborationService->addExistingUser($formAddExistingUser->get('user')->getData(), $projectId);
            return $this->redirectToRoute('manage_collaboration', ['projectId' => $projectId]);
        }
        return $this->redirectToRoute('manage_collaboration', ['projectId' => $projectId]);
    }

    #[Route('/{projectId}/collaborations/add-new', name: 'add_new_user')]
    public function addNewUser(
        Request $request,
        CollaborationService $collaborationService,
        $projectId
    ): Response {
        $formAddNewUser = $this->createForm(RegistrationFormType::class);
        $formAddNewUser->handleRequest($request);
        if ($formAddNewUser->isSubmitted() && $formAddNewUser->isValid()) {
            $collaborationService->addNewUser($formAddNewUser, $projectId);
            return $this->redirectToRoute('manage_collaboration', ['projectId' => $projectId]);
        }
        return $this->redirectToRoute('manage_collaboration', ['projectId' => $projectId]);
    }

    #[Route('/{projectId}/collaborations/{collaborationId}/delete', name: 'delete_collaboration')]
    public function deleteCollaboration(
        CollaborationService $collaborationService,
        $projectId,
        $collaborationId
    ): Response {
        $collaborationService->deleteCollaboration($collaborationId);
        return $this->redirectToRoute('manage_collaboration', ['projectId' => $projectId]);
    }
}