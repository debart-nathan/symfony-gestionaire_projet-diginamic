<?php
namespace App\Service;

use App\Entity\Project;
use App\Entity\Collaboration;
use Doctrine\ORM\EntityManagerInterface;

class ProjectService
{

    public function __construct(private EntityManagerInterface $entityManager)
    {

    }

    public function createProject(Project $project, $user)
    {
        // Create a new Collaboration
        $collaboration = new Collaboration();
        $collaboration->setUser($user);
        $collaboration->setProject($project);
        $collaboration->setIsAdmin(true);

        // Persist the new Collaboration
        $this->entityManager->persist($collaboration);

        $this->entityManager->persist($project);
        $this->entityManager->flush();

        return $project;
    }

    public function updateProject(Project $project)
    {
        $this->entityManager->flush();
    }

    public function deleteProject(Project $project)
    {
        // Remove the project
        $this->entityManager->remove($project);
        $this->entityManager->flush();
    }
}