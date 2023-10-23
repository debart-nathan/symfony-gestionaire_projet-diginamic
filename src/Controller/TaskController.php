<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\Project;
use App\Form\TaskType;
use App\Repository\CollaborationRepository;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/project/{projectId}')]
class TaskController extends AbstractController
{

    #[Route('task/new', name: 'task_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        CollaborationRepository $collaborationRepository,
        ProjectRepository $projectRepository,
        $projectId
    ): Response {
        $project = $projectRepository->find($projectId);
        $adminUser = $collaborationRepository->findAdminUserByProject($project);
        $currentUser = $this->getUser();

        dump($adminUser, $currentUser);
        if ($collaborationRepository->findAdminUserByProject($project) != $this->getUser()) {

            //return $this->redirectToRoute('list_project');
        }

        $task = new Task();
        $project->addTask($task); // Associate task with project
        $form = $this->createForm(TaskType::class, $task, [
            'userIsAdmin' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('list_task', ['projectId' => $projectId]);
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
            'project' => $project
        ]);
    }

    #[Route('task/{id}/edit', name: 'task_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Task $task,
        EntityManagerInterface $entityManager,
        CollaborationRepository $collaborationRepository,
        ProjectRepository $projectRepository,
        $projectId
    ): Response {
        $project = $projectRepository->find($projectId);
        if (
            $collaborationRepository->findAdminUserByProject($project) != $this->getUser() &&
            $task->getCollaboration()->getUser() != $this->getUser()
        ) {
            // Redirect to project list or show an error message
            return $this->redirectToRoute('list_project');
        }

        $form = $this->createForm(TaskType::class, $task, [
            'userIsAdmin' => $collaborationRepository->findAdminUserByProject($project) == $this->getUser()
        ]);
        $form->handleRequest($request);

        // Create delete form
        $deleteForm = $this->createFormBuilder()
            ->setAction($this->generateUrl('task_delete', ['id' => $task->getId(), 'projectId' => $projectId]))
            ->setMethod('POST')
            ->getForm();

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('list_task', ['projectId' => $projectId]);
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(), // Pass delete form to template
            'project' => $project
        ]);
    }

    #[Route('task/{id}/delete', name: 'task_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Task $task,
        EntityManagerInterface $entityManager,
        ProjectRepository $projectRepository,
        CollaborationRepository $collaborationRepository,
        $projectId
    ): Response {
        $project = $projectRepository->find($projectId);
        if (!$collaborationRepository->findAdminUserByProject($project)) {
            // Redirect to project list or show an error message
            return $this->redirectToRoute('list_project');
        }

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('task_delete', ['id' => $task->getId(), 'projectId' => $projectId]))
            ->setMethod('POST')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $project = $entityManager->getRepository(Project::class)->find($projectId);
            $project->removeTask($task); // Remove task from project
            $entityManager->remove($task);
            $entityManager->flush();

            return $this->redirectToRoute('list_task', ['projectId' => $projectId]);
        }


        return $this->redirectToRoute('task_edit', ['id' => $task->getId(), 'projectId' => $projectId]);
    }
}
