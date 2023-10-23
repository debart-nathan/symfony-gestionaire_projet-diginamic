<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    private ?Project $project = null;

    #[ORM\ManyToOne(targetEntity: Collaboration::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(name: "collaboration_id", referencedColumnName: "id", onDelete: "SET NULL")]
    private ?Collaboration $collaboration;

    #[ORM\Column(length: 255)]
    private ?string $state = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getproject(): ?Project
    {
        return $this->project;
    }

    public function setproject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get the value of collaboration
     */
    public function getCollaboration()
    {
        return $this->collaboration;
    }

    /**
     * Set the value of collaboration
     *
     * @return  self
     */
    public function setCollaboration($collaboration)
    {
        $this->collaboration = $collaboration;

        return $this;
    }
}
