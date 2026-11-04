<?php

namespace App\Entity;

use App\Repository\TypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TypeRepository::class)
 */
class Type
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
     * @ORM\OneToMany(targetEntity=Question::class, mappedBy="Type")
     */
    private $Type;

    /**
     * @ORM\OneToMany(targetEntity=ReponseBonus::class, mappedBy="Type")
     */
    private $Reponse;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    public function __construct()
    {
        $this->Type = new ArrayCollection();
        $this->Reponse = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getType(): Collection
    {
        return $this->Type;
    }

    public function addType(Question $type): self
    {
        if (!$this->Type->contains($type)) {
            $this->Type[] = $type;
            $type->setType($this);
        }

        return $this;
    }

    public function removeType(Question $type): self
    {
        if ($this->Type->removeElement($type)) {
            // set the owning side to null (unless already changed)
            if ($type->getType() === $this) {
                $type->setType(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ReponseBonus>
     */
    public function getReponse(): Collection
    {
        return $this->Reponse;
    }

    public function addReponse(ReponseBonus $reponse): self
    {
        if (!$this->Reponse->contains($reponse)) {
            $this->Reponse[] = $reponse;
            $reponse->setType($this);
        }

        return $this;
    }

    public function removeReponse(ReponseBonus $reponse): self
    {
        if ($this->Reponse->removeElement($reponse)) {
            // set the owning side to null (unless already changed)
            if ($reponse->getType() === $this) {
                $reponse->setType(null);
            }
        }

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

}
