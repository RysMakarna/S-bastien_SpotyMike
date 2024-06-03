<?php

namespace App\Controller;

use App\Entity\Song;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class SongController extends AbstractController
{

    private $entityManager;
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Song::class);
    }

    #[Route('/add/song', name: 'app_song_add',methods: ['POST'])]
    public function AddSong( Request $request): JsonResponse
    {
        $title = $request->get('title');
        $url = $request->get('url');
        $reponse =$this->repository->findOneBy(['title'=> $title,'url'=> $url]);
        $reponseurl =$this->repository->findOneBy(['url'=> $url]);
        if($reponse & $reponseurl ){
            return $this->json([
                "message" => 'existe',
            ]);
        }
        $song= new Song();
        $song->setAlbum($request->get('album_id'));
        $song->setPlaylistHasSong($request->get('playlist_has_song_id'));
        $song->setIdSong($request->get('id_song'));
        $song->setTitle($request->get('title'));
        $song->setUrl($request->get('url'));
        $song->setCover($request->get('cover'));
        $song->setVisibility($request->get('visibility'));
        $song->setCreateAt(new \DateTimeImmutable());
        $song->setVisibility($request->get('visibility'));

        $this->entityManager->persist($song);
        $this->entityManager->flush();
        return $this->json([
            'Ajouter!',
        ]);
    }
    #[Route('/update/song/{id}', name:'app_song_remove', methods: ['PUT'])]
    public function updateSong(int $id,Request $request ): JsonResponse
    {
        $song= $this->entityManager->getRepository(Song::class)->find($id);;
        if (!$song) {
          return $this->json([
              'message' => 'Aucune compte avec ce id à modifier !',
          ]);
        }
        $song->setVisibility($request->get('visibility'));
        $song->setCover($request->get('cover'));
        $song->setTitle($request->get('title'));
        $this->entityManager->flush();
        return $this->json([
          'message'=> 'modifier ',
        ]);
    }

    #[Route('/read/song', name: 'app_read_song')]
    public function readUser(): JsonResponse
    {
        $song = $this->entityManager->getRepository(Song::class)->findAll();

        $songArray = array_map(function ($song) {
            return $song->AllSong(); // Ensure you have a toArray() method in your User entity
        }, $song);

        return $this->json([
          $songArray,
        ]);
    }

}

