<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\ProjectRepository;
use App\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/project/{projectId}')]
class TaskController extends AbstractController
{


    public function __construct(private TaskService $taskService)
    {
    }

    #[Route('task/new', name: 'task_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        ProjectRepository $projectRepository,
        $projectId
    ): Response {
        $project = $projectRepository->find($projectId);
        $currentUser = $this->getUser();

        if (!$this->taskService->isAdmin($project, $currentUser)) {
            return $this->redirectToRoute('list_project');
        }

        $task = new Task();
        $project->addTask($task); // Associate task with project
        $form = $this->createForm(TaskType::class, $task, [
            'userIsAdmin' => true,
        ]);

        $form->handleRequest($request);
        if ($this->taskService->createTask($form, $task, $project)) {
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
        ProjectRepository $projectRepository,
        $projectId
    ): Response {
        $project = $projectRepository->find($projectId);
        $currentUser = $this->getUser();

        if (
            !$this->taskService->isAdmin($project, $currentUser) &&
            $task->getCollaboration()->getUser() != $currentUser
        ) {
            // Redirect to project list or show an error message
            return $this->redirectToRoute('list_project');
        }

         // Save old collaboration for check and update
        $oldCollaboration = $task->getCollaboration();

        $form = $this->createForm(TaskType::class, $task, [
            'userIsAdmin' => $this->taskService->isAdmin($project, $currentUser)
        ]);

        $form->handleRequest($request);
        if ($this->taskService->updateTask($form, $task,$oldCollaboration)) {
            return $this->redirectToRoute('list_task', ['projectId' => $projectId]);
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
            'project' => $project
        ]);
    }

    #[Route('task/{id}/delete', name: 'task_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Task $task,
        ProjectRepository $projectRepository,
        $projectId
    ): Response {
        $project = $projectRepository->find($projectId);
        $currentUser = $this->getUser();

        if (!$this->taskService->isAdmin($project, $currentUser)) {
            // Redirect to project list or show an error message
            return $this->redirectToRoute('list_project');
        }

        $this->taskService->deleteTask($task, $project);

        return $this->redirectToRoute('list_task', ['projectId' => $projectId]);
    }
}
