<?php

namespace App\Controller;

use App\Repository\CollaborationRepository;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectListController extends AbstractController
{
    #[Route('/project', name: 'list_project')]
    public function index(
        CollaborationRepository $collaborationRepository,
    ): Response {

        $collaborations = $collaborationRepository->findByUser($this->getUser());
        $projectsData = array_map(function ($collaboration) {
            $project= $collaboration->getProject();
            return [
                'id' => $project->getId(),
                'name' => $project->getName(),
                'description' => $project->getDescription(),
                'isAdmin' => $collaboration->getIsAdmin()
            ];
        }, $collaborations);

        return $this->render('project_list/index.html.twig', [
            'title' => 'Liste des projets',
            'projects' => $projectsData,
        ]);
    }
}
