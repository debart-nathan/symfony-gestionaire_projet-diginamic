<?php

namespace App\Service;

use App\Entity\Collaboration;
use App\Entity\Task;
use App\Entity\Project;
use App\Repository\CollaborationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

class TaskService
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private CollaborationRepository $collaborationRepository
    ) {

    }

    public function createTask(FormInterface $form, Task $task, Project $project)
    {
        if ($form->isSubmitted() && $form->isValid()) {
            // Get the Collaboration associated with the Task
            $collaboration = $task->getCollaboration();

            // If a Collaboration is associated with the Task, add the Task to the Collaboration
            if ($collaboration !== null) {
                $collaboration->addTask($task);
            }

            // Add the task to the project's tasks
            $project->addTask($task);

            $this->entityManager->persist($task);
            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    public function updateTask(FormInterface $form, Task $task, ?Collaboration $odlCollaboration)
    {
        if ($form->isSubmitted() && $form->isValid()) {
            // Get the Collaboration associated with the Task
            $collaboration = $task->getCollaboration();

            // If the collaboration changed
            if ($odlCollaboration != $task->getCollaboration()) {
                if($odlCollaboration !== null){
                    $odlCollaboration->removeTask($task);
                }
                if ($collaboration !== null) {
                    $collaboration->addTask($task);
                }
            }
            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    public function deleteTask(Task $task, Project $project)
    {
        $project->removeTask($task); // Remove task from project
        $this->entityManager->remove($task);
        $this->entityManager->flush();
    }

    public function isAdmin(Project $project, $user)
    {
        return $this->collaborationRepository->findAdminUserByProject($project) == $user;
    }
}
