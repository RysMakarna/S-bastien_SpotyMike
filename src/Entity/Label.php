<?php

namespace App\Entity;

use App\Repository\LabelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LabelRepository::class)]
class Label
{
    #[ORM\Id]
    #[ORM\Column(name: "id_label", type:"string", length: 20)]
    private ?string $id_label = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $UpdateAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $year = null;

    #[ORM\OneToMany(targetEntity: ArtistHasLabel::class, mappedBy: 'id_label')]
    private Collection $artistHasLabels;

    public function __construct()
    {
        $this->artistHasLabels = new ArrayCollection();
    }

    public function getIdLabel(): ?string
    {
        return $this->id_label;
    }

    public function setIdLabel(string $id_label): static
    {
        $this->id_label = $id_label;

        return $this;
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

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeImmutable $createAt): static
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeImmutable
    {
        return $this->UpdateAt;
    }

    public function setUpdateAt(\DateTimeImmutable $UpdateAt): static
    {
        $this->UpdateAt = $UpdateAt;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): static
    {
        $this->year = $year;

        return $this;
    }

    /**
     * @return Collection<int, ArtistHasLabel>
     */
    public function getArtistHasLabels(): Collection
    {
        return $this->artistHasLabels;
    }

    public function addArtistHasLabel(ArtistHasLabel $artistHasLabel): static
    {
        if (!$this->artistHasLabels->contains($artistHasLabel)) {
            $this->artistHasLabels->add($artistHasLabel);
            $artistHasLabel->setIdLabel($this);
        }

        return $this;
    }

    public function removeArtistHasLabel(ArtistHasLabel $artistHasLabel): static
    {
        if ($this->artistHasLabels->removeElement($artistHasLabel)) {
            // set the owning side to null (unless already changed)
            if ($artistHasLabel->getIdLabel() === $this) {
                $artistHasLabel->setIdLabel(null);
            }
        }

        return $this;
    }
}
