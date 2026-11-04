<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=QuestionRepository::class)
 */
class Question
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $designation;

    /**
     * @ORM\ManyToOne(targetEntity=Type::class, inversedBy="Type")
     */
    private $Type;

    /**
     * @ORM\OneToMany(targetEntity=Reponse::class, mappedBy="Question")
     */
    private $Question;

    public function __construct()
    {
        $this->Question = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->Type;
    }

    public function setType(?Type $Type): self
    {
        $this->Type = $Type;

        return $this;
    }

    /**
     * @return Collection<int, Reponse>
     */
    public function getQuestion(): Collection
    {
        return $this->Question;
    }

    public function addQuestion(Reponse $question): self
    {
        if (!$this->Question->contains($question)) {
            $this->Question[] = $question;
            $question->setQuestion($this);
        }

        return $this;
    }

    public function removeQuestion(Reponse $question): self
    {
        if ($this->Question->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getQuestion() === $this) {
                $question->setQuestion(null);
            }
        }

        return $this;
    }

}
