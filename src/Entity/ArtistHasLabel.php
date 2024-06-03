<?php

namespace App\Entity;

use App\Repository\ArtistHasLabelRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;


#[ORM\Entity(repositoryClass: ArtistHasLabelRepository::class)]
class ArtistHasLabel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'artistHasLabels')]
    #[ORM\JoinColumn(referencedColumnName: 'id_label', nullable: false)]
    private ?Label $id_label = null;

    #[ORM\ManyToOne(inversedBy: 'artistHasLabels')]
    #[ORM\JoinColumn(referencedColumnName: 'artistId', nullable: false)]
    private ?Artist $idArtist = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $addedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $quittedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdLabel(): ?Label
    {
        return $this->id_label;
    }

    public function setIdLabel(?Label $id_label): static
    {
        $this->id_label = $id_label;

        return $this;
    }

    public function getIdArtist(): ?Artist
    {
        return $this->idArtist;
    }

    public function setIdArtist(?Artist $idArtist): static
    {
        $this->idArtist = $idArtist;

        return $this;
    }

    public function getAddedAt(): ?\DateTimeImmutable
    {
        return $this->addedAt;
    }

    public function setAddedAt(\DateTimeImmutable $addedAt): static
    {
        $this->addedAt = $addedAt;

        return $this;
    }

    public function getUQuittedAt(): ?\DateTimeInterface
    {
        return $this->quittedAt;
    }

    public function setQuittedAt(\DateTimeInterface $quittedAt): static
    {
        $this->quittedAt = $quittedAt;

        return $this;
    }
}
