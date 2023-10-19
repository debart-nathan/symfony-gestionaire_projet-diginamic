<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/project')]
class ProjectController extends AbstractController
{
    #[Route('/', name: 'list_project')]
    public function index(ProjectRepository $projectRepository): Response
    {

        $projects = $projectRepository->findByUser($this->getUser());

        $projectsData = array_map(function ($project) {
            return [
                'id' => $project->getId(),
                'name' => $project->getName(),
                'description' => $project->getDescription()
            ];
        }, $projects);

        return $this->render('project/index.html.twig', [
            'title' => 'Liste des projets',
            'projects' => $projectsData,
        ]);
    }

    #[Route('/new', name: 'project_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $project->setUser($this->getUser());
            $entityManager->persist($project);
            $entityManager->flush();

            return $this->redirectToRoute('list_project');
        }

        return $this->render('project/new.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'project_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        if ($project->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('list_project');
        }
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        // Create delete form
        $deleteForm = $this->createFormBuilder()
            ->setAction($this->generateUrl('project_delete', ['id' => $project->getId()]))
            ->setMethod('POST')
            ->getForm();

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('list_project');
        }

        $projectData = [
            'id' => $project->getId(),
            'name' => $project->getName(),
            'description' => $project->getDescription()
        ];

        return $this->render('project/edit.html.twig', [
            'project' => $projectData,
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(), // Pass delete form to template
        ]);
    }

    #[Route('{id}/delete/', name: 'project_delete', methods: ['POST'])]
    public function delete(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        if ($project->getUser() !== $this->getUser()) {
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
}
