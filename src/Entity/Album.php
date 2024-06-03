<?php

namespace App\Entity;

use App\Repository\AlbumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: AlbumRepository::class)]
class Album
{

    #[ORM\Id]
    #[ORM\Column(name:'idAlbum',type:"string", length: 90)]
    private ?string $idAlbum = null;

    #[ORM\Column(length: 90)]
    private ?string $nom = null;

    #[ORM\Column(length: 20)]
    private ?string $categ = null;

    #[ORM\Column(length: 125)]
    private ?string $cover = null;
    #[ORM\Column]
    private ?int $actif = 1;

    #[ORM\Column]
    private ?int $year = 2024;

    #[ORM\Column]
    private ?\DateTimeImmutable $createAt = null;

    #[ORM\OneToMany(targetEntity: Song::class, mappedBy: 'album')]
    private Collection $song_idSong;

    #[ORM\Column]
    private ?bool $visibility = true;

    #[ORM\ManyToOne(inversedBy: 'album')]
    #[ORM\JoinColumn(name: 'artist_id', referencedColumnName: 'artistId', nullable: false)]
    private ?Artist $Artist_User_idUser = null;


    public function __construct()
    {
        $this->song_idSong = new ArrayCollection();
    }

    public function getIdAlbum(): ?string
    {
        return $this->idAlbum;
    }

    public function setIdAlbum(string $idAlbum): static
    {
        $this->idAlbum = $idAlbum;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCateg(): ?string
    {
        return $this->categ;
    }

    public function setCateg(string $categ): static
    {
        $this->categ = $categ;

        return $this;
    }

    public function getCover(): ?string
    {
        return $this->cover;
    }

    public function setCover(string $cover): static
    {
        $this->cover = $cover;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): static
    {
        $this->year = $year;

        return $this;
    }
    public function getActif(): ?int
    {
        return $this->actif;
    }

    public function setActif(?int $actif): static
    {
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

    /**
     * @return Collection<int, Song>
     */
    public function getSongIdSong(): Collection
    {
        return $this->song_idSong;
    }

    public function addSongIdSong(Song $songIdSong): static
    {
        if (!$this->song_idSong->contains($songIdSong)) {
            $this->song_idSong->add($songIdSong);
            $songIdSong->setAlbum($this);
        }

        return $this;
    }

    public function removeSongIdSong(Song $songIdSong): static
    {
        if ($this->song_idSong->removeElement($songIdSong)) {
            // set the owning side to null (unless already changed)
            if ($songIdSong->getAlbum() === $this) {
                $songIdSong->setAlbum(null);
            }
        }

        return $this;
    }
    public function serializer($name)
    {
        $songs = $this->getSongIdSong();
        $serializedSongs = [];
        foreach ($songs as $song) {
            $serializedSongs[] = $song->SerializerUser();
        }
        return [
            "nom" => $this->getNom(),
            "categ" => $this->getCateg(),
            "cover" => $this->getCover(),
            "label"=> $name,
            "song" => $songs = null ? [] : $serializedSongs,
            "year" => $this->getYear(),
            "createAt" => $this->getCreateAt()->format('d-m-Y'),
        ];
    }

    public function serialOneAlbum()
    {   
        $songs = $this->getSongIdSong();
        $serializedSongs = [];
        foreach ($songs as $song) {
            $serializedSongs[] = $song->SerializerUser();
        }
        return [
            "id" => $this->getIdAlbum(),
            "nom" => $this->getNom(),
            "categ" => $this->getCateg(),
            "label" => $this->getArtistIdUser()->getArtistHasLabel()->last(),
            "cover" => $this->getCover(),
            "year"=> $this->getYear(),
            "createAt"=> $this->getCreateAt()->format('d-m-Y'),
            "songs"=> $songs = null ? [] : $serializedSongs,
            "artist"=>$this->getArtistIdUser()->serialAlbum(),
        ];
    }

    public function isVisibility(): ?bool
    {
        return $this->visibility;
    }

    public function setVisibility(bool $visibility): static
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getArtistIdUser(): ?Artist
    {
        return $this->Artist_User_idUser;
    }

    public function setArtistIdUser(?Artist $Artist_User_idUser): static
    {
        $this->Artist_User_idUser = $Artist_User_idUser;

        return $this;
    }

}