<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\SongRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: SongRepository::class)]
class Song
{
    #[ORM\Id]
    #[ORM\Column(name: 'idSong', type:"string", length: 90)]
    private ?string $idSong = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 125)]
    private ?string $url = null;

    #[ORM\Column(length: 125)]
    private ?string $cover = null;

    #[ORM\Column]
    private ?bool $visibility = true;

    #[ORM\Column]
    private ?int $actif = 1;

    #[ORM\Column]
    private ?\DateTimeImmutable $createAt = null;

    #[ORM\ManyToMany(targetEntity: Artist::class, inversedBy: 'songs')]
    #[ORM\JoinTable(name: "artist_Id")]
    #[ORM\JoinColumn(name: "song_id", referencedColumnName: "idSong")]
    #[ORM\InverseJoinColumn(name: "artist_id", referencedColumnName: "artistId")]
    private Collection $Artist_idUser;

    #[ORM\ManyToOne(inversedBy: 'song_idSong', cascade: ['remove'])]
    #[ORM\JoinColumn(name: 'albumId', referencedColumnName: 'idAlbum', nullable: false)]
    private ?Album $album = null;

    #[ORM\ManyToOne(inversedBy: 'Song_idSong')]
    private ?PlaylistHasSong $playlistHasSong = null;

    public function __construct()
    {
        $this->Artist_idUser = new ArrayCollection();
    }

    public function getIdSong(): ?string
    {
        return $this->idSong;
    }

    public function setIdSong(string $idSong): static
    {
        $this->idSong = $idSong;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

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

    public function isVisibility(): ?bool
    {
        return $this->visibility;
    }

    public function setVisibility(bool $visibility): static
    {
        $this->visibility = $visibility;

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
     * @return Collection<int, Artist>
     */
    public function getArtistIdUser(): Collection
    {
        return $this->Artist_idUser;
    }

    public function addArtistIdUser(Artist $artistIdUser): static
    {
        if (!$this->Artist_idUser->contains($artistIdUser)) {
            $this->Artist_idUser->add($artistIdUser);
        }

        return $this;
    }

    public function removeArtistIdUser(Artist $artistIdUser): static
    {
        $this->Artist_idUser->removeElement($artistIdUser);

        return $this;
    }

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): static
    {
        $this->album = $album;

        return $this;
    }

    public function getPlaylistHasSong(): ?PlaylistHasSong
    {
        return $this->playlistHasSong;
    }

    public function setPlaylistHasSong(?PlaylistHasSong $playlistHasSong): static
    {
        $this->playlistHasSong = $playlistHasSong;

        return $this;
    }
    public function Serializer()
    {
        return[
            "id"=> $this->getIdSong(),
            "cover"=>$this->getCover(),
            "title"=>$this->getTitle(),
            "createAt"=>$this->getCreateAt()->format('d-m-Y'),
        ];
    }
    public function SerializerUser()
    {   
        $feats = $this->getArtistIdUser();
        $serializedFeat = [];
        foreach($feats as $feat){
            $serializedFeat[] = $feat->serializer();
        }
        return[
            "id"=> $this->getIdSong(),
            "cover"=>$this->getCover(),
            "title"=>$this->getTitle(),
            "createAt"=>$this->getCreateAt()->format('d-m-Y'),
            "featuring" => $feat = null ? [] : $serializedFeat,
        ];
    }
}