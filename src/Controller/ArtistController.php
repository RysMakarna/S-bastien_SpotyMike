<?php

namespace App\Controller;

use App\Entity\ArtistHasLabel;
use App\Entity\User;
use App\Entity\Artist;
use App\Entity\Song;
use App\Entity\Album;
use App\Entity\Label;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArtistController extends AbstractController
{
    private $entityManager;
    private $tokenVerifier;

    public function __construct(EntityManagerInterface $entityManager, TokenService $tokenService)
    {
        $this->entityManager = $entityManager;
        $this->tokenVerifier = $tokenService;
    }
    #[Route('/artist', name: 'artist_get', methods: 'GET')]
    public function read(Request $request): JsonResponse
    {
        $currentUser = $this->tokenVerifier->checkToken($request, null);
        if (gettype($currentUser) == 'boolean') {
            return $this->tokenVerifier->sendJsonErrorToken();
        }
        $currentPage = $request->get('currentPage');
        if(!is_numeric($currentPage) || $currentPage < 0) {
            return $this->json([
                'error'=>true,
                'message'=>"Le paramètre de pagination est invalide.Veuillez fournir un numéro de page valide"
            ],400);
        }
        $serializedArtists = [];
        $page = $request->query->getInt('page', $currentPage);
        $limit = $request->query->getInt('limit', 5);
        $totalArtist = $this->entityManager->getRepository(Artist::class)->countArtist();
        $totalPages = ceil($totalArtist/$limit);
        $allArtists = $this->entityManager->getRepository(Artist::class)->findAllWithPagination($page, $limit); // tous les informations de l'artiste..
        if($currentPage > $totalPages) {
            return $this->json([
                'error'=>true,
                'message'=> "Aucun artiste trouvé pour la page demandée"
            ],404);
        }
        //dd($allArtists);
        $serializedArtists = [];
        //dd($allArtists);
        foreach ($allArtists as $artist) {
            for( $i = 0; $i < count($artist)-1; $i++ ){
                //dd($artist);
                array_push($serializedArtists, $artist[$i]->ArtistSerealizer($artist["name"]));
            }
        }
        //dd($serializedArtists);
        /*
        if(){
            return $this->json([
                'error'=>true,
                'message'=> 'Aucun artiste trouvé pour la page demandée'

            ],404);
        }*/

        return $this->json([
            'error' => false,
            'artist' => $serializedArtists,
            'message'=>'Informations des artistes récupérées avec succès',
            'pagination'=>[
                'currentPage'=>$page,
                'totalPages'=>$totalPages,
                'totalArtists'=> $totalArtist,
            ],
        ], 200);
    }

    #[Route('/artist', name: 'app_artist', methods: 'POST')]
    public function readOne(Request $request): JsonResponse
    {
        $regex_idLabel = '/^12[0-9][a-zA-Z]$/';
        $currentUser = $this->tokenVerifier->checkToken($request, null);
        $urepository = $this->entityManager->getRepository(Artist::class);
        if (gettype($currentUser) == 'boolean') {
            return $this->tokenVerifier->sendJsonErrorToken();
        }
        $artist = $urepository->findOneBy(["User_idUser" => $currentUser->getIdUser()]);
        if ($artist) {
            parse_str($request->getContent(), $artistData);
            if ($this->verifyKeys($artistData, 2) === false){
                return $this->sendError400(1);
            }
            if ($artist->getActif() === 0) {
                return $this->json([
                    "error" => true,
                    "message" => "Vous n'êtes pas autorisé à accéder aux informations de cet artiste.",
                ], 403);
            }
            if (isset($artistData['avatar'])){
                if ($artistData['avatar']) {
                    $explodeData = explode(",", $artistData['avatar']);
                    if (count($explodeData) == 2) {
                        # Verify File Extension
                        $reexplodeData = explode(";", $explodeData[0]);
                        $fileExt = explode("/", $reexplodeData[0]);
    
                        $fileExt[1] == "png" ? "png" : ($fileExt[1] == "jpeg" ? "jpeg" : $this->sendError422(2));
    
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
    
                        $chemin = $this->getParameter('upload_directory') . '/' . $artist->getFullname();
                        file_put_contents($chemin . '/avatar.' + $fileExt[1], $file);
                    }
                }
            }

            if (isset($artistData['fullname'])) {
                $otherArtist = $urepository->findOneBy(["fullname" => $artistData['fullname']]);
                if ($artist->getUserIdUser() != $otherArtist->getUserIdUser()) {
                    return $this->json([
                        "error" => true,
                        "message" => "Le nom d'artiste est déjà utilisé. Veuillez choisir un autre nom.",
                    ], 409);
                }
                if (!preg_match("/^[a-zA-ZÀ-ÿ\-]+$/", $artistData['fullname'])) {
                    return $this->sendError400(1);
                }
                $artist->setFullname($artistData['fullname']);
            }
            if (isset($artistData['description'])) {
                if (!preg_match("/^[a-zA-ZÀ-ÿ\ -]+$/", $artistData['description'])) {
                    //dd("why");
                    return $this->sendError400(1);
                }
                $artist->setDescription($artistData['description']);
            }

            if (isset($artistData['label'])) {
                $Label = $this->entityManager->getRepository(Label::class)->findOneBy(['id_label' => $artistData['label']]);
                if (!$Label) {
                    return $this->sendError400(3);
                }
                $oldLabel = $this->entityManager->getRepository(ArtistHasLabel::class)->findOneBy(['id_User' => $artist->getUserIdUser(), 'quittedAt' => null]);
                $oldLabel->setQuittedAt(new DateTime());

                $newLabelOfArtist = new ArtistHasLabel();
                $newLabelOfArtist->setIdLabel($request->get('id_label'));
                $newLabelOfArtist->setIdArtist($artist->getUserIdUserId());
                $newLabelOfArtist->setAddedAt(new \DateTimeImmutable());
                
                $this->entityManager->persist($oldLabel);
                $this->entityManager->persist($newLabelOfArtist);
            }

            $this->entityManager->persist($artist);
            $this->entityManager->flush();
            return $this->json([
                "error" => false,
                "message" => "Les informations de l'artiste ont été mises à jour avec succès."
            ], 200);

        } else {
            parse_str($request->getContent(), $artistData);

            $this->verifyKeys($artistData, 1) == true ? true : $this->sendError400(4);
            if (empty($artistData["label"]) || empty($artistData["fullname"])) {
                return $this->sendError400(2);
            }

            //verification du format de id_label 
            $checkLabel = $this->entityManager->getRepository(Label::class)->findOneBy(['id_label' => $artistData["label"]]);
            if ($checkLabel === null) {
                return $this->sendError400(3);
            }
            if (!preg_match('^/[\p{P}\a-zA-ZÀ-ÿ0-9\p{S}\µ]+$/^', $artistData["fullname"]))
                $currentDate = new DateTime();
            $age = $currentDate->diff($currentUser->getBirthday());
            if (($age->y) < 16) {
                return $this->json([
                    'error' => true,
                    'message' => "Vous devez avoir au moins 16 ans pour être artiste."

                ], 403);
            }
            $artistFullname = $this->entityManager->getRepository(Artist::class)->GetExiteFullname($artistData["fullname"]);
            if ($artistFullname[1] != 0) {
                return $this->json([
                    'error' => true,
                    'message' => 'Ce nom d\'artist est déjà pris. Veuillez en choisir un autre.'
                ], 409);
            }
            if (isset($artistData['avatar'])) {
                $explodeData = explode(",", $artistData['avatar']);
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
    
                    $chemin = $this->getParameter('upload_directory') . '/' . $artistData["fullname"];
                    mkdir($chemin);
                    file_put_contents($chemin . '/avatar.' + $fileExt[1], $file);
                } else {
                    return $this->sendError422(1);
                }
            }
            
            $newArtist = new Artist();
            $newArtist->setFullname($artistData["fullname"]);
            if (isset($artistData["description"])){
                $newArtist->setDescription($artistData["description"]);
            }
            $newArtist->setUserIdUser($currentUser);
            $newArtist->setCreateAt(new \DateTimeImmutable());
            $newArtist->setUpdateAt(new DateTime());

            $this->entityManager->persist($newArtist);
            $this->entityManager->flush();
            $artistId = $this->entityManager->getRepository(Artist::class)->findOneBySomeField($currentUser->getIdUser());

            $labelOfArtist = new ArtistHasLabel();
            $labelId = $this->entityManager->getRepository(Label::class)->findOneBy(['id_label' => $artistData["label"]]);
            $labelOfArtist->setIdLabel($labelId);
            $labelOfArtist->setIdArtist($artistId);
            $labelOfArtist->setAddedAt(new \DateTimeImmutable());
            $this->entityManager->persist($labelOfArtist);
            $this->entityManager->flush();

            return $this->json([
                "success" => true,
                'message' => "Votre compte d'artiste a été créé avec succès. Bienvenue dans notre communauté d'artiste!",
                'artist_id' => $artistId->getUserIdUser(), // Supposant que l'ID de l'artiste est 1, ajustez selon la logique appropriée
            ], 201); // Utilisez 200 pour indiquer le succès
        }
    }

    #[Route('/artist', name: 'app_delete_artist', methods: 'DELETE')]
    public function deleteOne(Request $request): JsonResponse{
        $currentUser = $this->tokenVerifier->checkToken($request, null);
        $urepository = $this->entityManager->getRepository(Artist::class);
        if (gettype($currentUser) == 'boolean') {
            return $this->tokenVerifier->sendJsonErrorToken();
        }
        $artist = $urepository->findOneBy(["User_idUser" => $currentUser->getIdUser()]);

        if($artist === null){
            return $this->json([
                "error"=>true,
                "message"=>"Compte artiste non trouvé. Vérifiez les informations fournies et réessayer.",
            ],404);
        }

        return $this->json([
            "success"=> true,
            "message"=> "Le compte artiste a été désactivé avedc succès.",
        ],200);
    }

    private function verifyKeys($requestBody, int $obli)
    {
                $obligatoryKeys = ['label', 'fullname'];
                $allowedKeys = ['description', 'avatar'];
                $keys = array_keys($requestBody);
                $resultGood = 0;
                foreach ($keys as $key) {
                    if (in_array($key, $obligatoryKeys)) {
                        $resultGood++;
                    } elseif (in_array($key, $allowedKeys)) {
                        $resultGood++;
                    } else {
                        return false;
                    }
                }
            switch ($obli) {
                case 1:
                    if ($resultGood >=2) {
                        return true;
                    }
            case 2:
                if ($resultGood >= 1){
                    return true;
                }
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
                    "message" => "L'id du label et le fullname sont obligatoires"
                ], 400);
            case 3:
                return $this->json([
                    'error' => true,
                    'message' => 'Le format de l\'id du label est invalide.',
                ], 400);
            case 4:
                return $this->json([
                    'error' => true,
                    'message' => 'Les données fournies sont invalides ou incomplètes',
                ], 400);

        }
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

}
