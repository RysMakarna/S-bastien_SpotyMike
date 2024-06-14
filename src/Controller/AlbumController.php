<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Artist;
use App\Repository\SongRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class AlbumController extends AbstractController
{
    private $entityManager;
    private $tokenVerifier;
    private $songRepository;

    public function __construct(EntityManagerInterface $entityManager, TokenService  $tokenService, SongRepository $songRepository)
    {
        $this->entityManager = $entityManager;
        $this->tokenVerifier = $tokenService;
        $this->songRepository = $songRepository;
    }

    #[Route('/album', name: 'app_album')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/AlbumController.php',
        ]);
    }

    #[Route('/album/{id}', name: 'app_album', methods: ['GET'])]
    public function fetchOne(Request $request, string $id): JsonResponse
    {
        $currentUser = $this->tokenVerifier->checkToken($request);
        if (gettype($currentUser) == 'boolean') {
            return $this->json($this->tokenVerifier->sendJsonErrorToken());
        }
        parse_str($request->getContent(), $albumData);
        
        if(!isset($id)){
            return $this->json([
                "error"=>true,
                "message"=> "L'id de l'album est obligatoire pour cette requête.",
            ], 400);
        }

        $album = $this->entityManager->getRepository(Album::class)->findByObne($id);
        if($album === null || $album->getActif() === 0 ){
            return $this->json([
                "error"=>true,
                "message"=> "L'album non trouvé. Vérifiez les informations fournies et réessayez",
            ],404);
        }

        return $this->json([
            "error"=>false,
            'album' => $album->serialOneAlbum(),
        ], 200);
    }

    #[Route('/album', name:'add_album', methods: ['POST'])]
    public function addAlbum(Request $request): JsonResponse
    {
        $currentUser = $this->tokenVerifier->checkToken($request);
        $urepository = $this->entityManager->getRepository(Artist::class);
        $alrepository = $this->entityManager->getRepository(Album::class);

        if (gettype($currentUser) == 'boolean') {
            return $this->json($this->tokenVerifier->sendJsonErrorToken());
        }
        $artist = $urepository->findOneBy(["User_idUser" => $currentUser->getIdUser()]);
        if ($artist->getActif() === 0) return $this->json([
            "error"=>true,
            "message"=>"Vous n'avez pas l'autorisation pou accéder à cet album.",
        ], 403);

        parse_str($request->getContent(), $albumData);

        if($this->verifyKeys($albumData) == false){
            return $this->sendError400(1);
        }
        
        if(preg_match("/^[\w\W]+$/", $albumData['title']) || preg_match("/^[\w\W]+$/", $albumData['categorie'])){
            return $this->sendError422(4);
        }

        $otherAlbum = $alrepository->findOneBy(["nom" => $albumData['title']]);
        if ($otherAlbum !== null) return $this->json([
            "error"=>true,
            "message"=> "Ce titre est déjà pris. Veuillez en choisir un autre.",
        ], 409);

        $this->verifyCateg($albumData['categorie']) == true ? true : $this->sendError400(3);

        if($albumData['visibility'] !== "0" && $albumData['visibility'] !== "1"){
            return $this->sendError400(2);
        }

        if($this->verifyCateg($albumData["categorie"]) == false){
            return $this->sendError400(3);
        }
        

        $album = new Album();
        $album->setArtistIdUser($currentUser->getIdUser());
        $albumId = uniqid();
        $explodeData = explode(",", $albumData['cover']);
        if (count($explodeData) == 2) {
            # Verify File Extension
            $reexplodeData = explode(";", $explodeData[0]);
            $fileExt = explode("/", $reexplodeData[0]);

            if ($fileExt[1] != "png" && $fileExt[1] != "jpeg"){
                return $this->sendError422(2);
            }

            $base64IsValid = imagecreatefromstring(base64_decode($explodeData[1], true));
            # Check if Base64 string can be decoded
            if ($base64IsValid === false) {
                return $this->sendError422(1);
            }
            $file = base64_decode($explodeData[1]);

            # Check if file size is correct
            $fileSize = ((strlen($file) * 0.75) / 1024) / 1024;
            if (number_format($fileSize, 1) < 1.0 || number_format($fileSize, 1) >= 8.0) {
                return $this->sendError422(3);
            }

            $chemin = $this->getParameter('cover_directory') . '/' . $artist->getFullname() . '-' . $albumId;
            mkdir($chemin);
            file_put_contents($chemin . '/Cover.' + $fileExt[1], $file);
        }
        $album->setIdAlbum($albumId);
        $album->setNom($albumData['title']);
        $album->setActif($albumData['visibility']);
        $album->setCateg($albumData['categorie']);
        $currentYear = new \DateTime;
        $album->setYear($currentYear->format("Y"));
        $album->setCreateAt(new \DateTimeImmutable);

        $this->entityManager->persist($album);
        $this->entityManager->flush();

        

        return $this->json([
            "error"=>false,
            'message' => "Album créé avec succès.",
            'id' => $album->getIdAlbum(), // Supposant que l'ID de l'artiste est 1, ajustez selon la logique appropriée
        ], 201);

    }

    private function verifyKeys($requestBody)
    {
        $obligatoryKeys = ['visibility', 'cover', "title", "categorie"];
                $keys = array_keys($requestBody);
                $resultGood = 0;
                foreach ($keys as $key) {
                    if (in_array($key, $obligatoryKeys)) {
                        $resultGood++;
                    } else {
                        return false;;
                    }
                }
                if ($resultGood == 4){
                    return true;
                }
                return false;
    }

    private function verifyCateg($categorie){
        $categContent = ['rap', "r'n'b", "gospel", "soul", "country", "hip hop", "jazz", "le Mike"];
        if (!in_array($categorie, $categContent)) {
            return false;
        }
        return true;
    }

    private function sendError422(int $errorCode)
    {
        switch ($errorCode) {
            case 1:
                return $this->json([
                    "error" => true,
                    "message" => "Le serveur ne peut pas décoder le contenu base64 en fichier binaire.",
                ], 422);
            case 2:
                return $this->json([
                    "error" => true,
                    "message" => "Erreur sur le format du fichier qui n'est pas pris en compte.",
                ], 422);
            case 3:
                return $this->json([
                    "error" => true,
                    "message" => "Le fichier envoyé est trop ou pas assez volumineux. Vous devez respecter la taille entre 1Mb et 7Mb.",
                ], 422);
        }
    }

    private function sendError400(int $errorCode)
    {
        switch ($errorCode) {
            case 1:
                return $this->json([
                    "error" => true,
                    "message" => "Les paramètres fournis sont invalides. Veuillez vérifier les données soumises.",
                ], 400);
            case 2:
                return $this->json([
                    "error" => true,
                    "message" => "la valeur du champ visibility est invalide. Les valeurs autorisées sont 0 pour invisible, 1 pour visible."
                ], 400);
            case 3:
                return $this->json([
                    'error' => true,
                    'message' => "Les catégories ciblées sont invalides",
                ], 400);
            case 4:

        }

    }

    public function findSongsByAlbum($id): Response
    {
        $songs = $this->songRepository->findAllSongByAlbum($id);

        return $this->json($songs);
    }
}
