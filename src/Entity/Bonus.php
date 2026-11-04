<?php

namespace App\Entity;

use App\Repository\BonusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BonusRepository::class)
 */
class Bonus
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
     * @ORM\OneToMany(targetEntity=ReponseBonus::class, mappedBy="Bonus")
     */
    private $Bonus;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    public function __construct()
    {
        $this->Bonus = new ArrayCollection();
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
     * @return Collection<int, ReponseBonus>
     */
    public function getBonus(): Collection
    {
        return $this->Bonus;
    }

    public function addBonu(ReponseBonus $bonu): self
    {
        if (!$this->Bonus->contains($bonu)) {
            $this->Bonus[] = $bonu;
            $bonu->setBonus($this);
        }

        return $this;
    }

    public function removeBonu(ReponseBonus $bonu): self
    {
        if ($this->Bonus->removeElement($bonu)) {
            // set the owning side to null (unless already changed)
            if ($bonu->getBonus() === $this) {
                $bonu->setBonus(null);
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
