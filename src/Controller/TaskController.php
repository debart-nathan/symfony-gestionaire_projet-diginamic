<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\Project;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/project/{projectId}/task')]
class TaskController extends AbstractController
{
    #[Route('/', name: 'list_task')]
    public function index(TaskRepository $taskRepository, EntityManagerInterface $entityManager, $projectId): Response
    {
        
        $project = $entityManager->getRepository(Project::class)->find($projectId);
        if ($project->getUser() !== $this->getUser()) {
            // Redirect to project list or show an error message
            return $this->redirectToRoute('list_project');
        }

        $tasks = $taskRepository->findBy(['projet' => $projectId]);
    
        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
            'project' => $project
        ]);
    }

    #[Route('/new', name: 'task_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, $projectId): Response
    {
        $project = $entityManager->getRepository(Project::class)->find($projectId);
        if ($project->getUser() !== $this->getUser()) {
            // Redirect to project list or show an error message
            return $this->redirectToRoute('list_project');
        }

        $task = new Task();
        $project->addTask($task); // Associate task with project
        $form = $this->createForm(TaskType::class, $task);
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

    #[Route('/{id}/edit', name: 'task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Task $task, EntityManagerInterface $entityManager, $projectId): Response
    {
        $project = $entityManager->getRepository(Project::class)->find($projectId);
        if ($project->getUser() !== $this->getUser()) {
            // Redirect to project list or show an error message
            return $this->redirectToRoute('list_project');
        }

        $form = $this->createForm(TaskType::class, $task);
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

    #[Route('/{id}/delete', name: 'task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task, EntityManagerInterface $entityManager, $projectId): Response
    {
        $project = $entityManager->getRepository(Project::class)->find($projectId);
        if ($project->getUser() !== $this->getUser()) {
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
