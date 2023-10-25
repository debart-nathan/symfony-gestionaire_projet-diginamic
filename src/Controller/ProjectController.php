<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\CollaborationRepository;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ProjectService;

#[Route('/project')]
class ProjectController extends AbstractController
{
    public function __construct(private ProjectService $projectService)
    {
        
    }


    #[Route('/new', name: 'project_new', methods: ['GET', 'POST'])]
    public function new(Request $request)
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->projectService->createProject($project, $this->getUser());

            return $this->redirectToRoute('list_project');
        }

        return $this->render('project/new.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'project_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Project $project)
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->projectService->updateProject($project);

            return $this->redirectToRoute('list_project');
        }

        return $this->render('project/edit.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
    }

    #[Route('{id}/delete/', name: 'project_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Project $project,
        EntityManagerInterface $entityManager,
        CollaborationRepository $collaborationRepository
    ): Response {

        $adminUser = $collaborationRepository->findAdminUserByProject($project);

        if ($adminUser !== $this->getUser()) {
            // Redirect to project list
            return $this->redirectToRoute('list_project');
        }

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('project_delete', ['id' => $project->getId()]))
            ->setMethod('POST')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->remove($project);
            $entityManager->flush();

            return $this->redirectToRoute('list_project');
        }


        return $this->redirectToRoute('project_edit', ['id' => $project->getId()]);
    }



    #[Route('/{projectId}', name: 'list_task')]
    public function index(
        TaskRepository $taskRepository,
        ProjectRepository $projectRepository,
        CollaborationRepository $collaborationRepository,
        $projectId
    ): Response {

        $project = $projectRepository->find($projectId);
        if (!$project->isCollaborator($this->getUser())) {
            // Redirect to project list or show an error message
            return $this->redirectToRoute('list_project');
        }

        $tasks = $taskRepository->findBy(['project' => $projectId]);

        $collaborations = $collaborationRepository->findBy(['project' => $projectId]);

        $adminUser = $collaborationRepository->findAdminUserByProject($project);
        $isUserAdmin = $adminUser === $this->getUser();

        return $this->render('project/index.html.twig', [
            'tasks' => $tasks,
            'project' => $project,
            'collaborations' => $collaborations,
            'isUserAdmin' => $isUserAdmin,
        ]);
    }
}