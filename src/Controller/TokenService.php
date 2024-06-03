<?php
namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use function Symfony\Component\Clock\now;

class TokenService extends AbstractController
{
    private $jwtManager;
    private $jwtProvider;
    private $userRepository;

    public function __construct(JWTTokenManagerInterface $jwtManager, JWSProviderInterface $jwtProvider, UserRepository $userRepository)
    {
        $this->jwtManager = $jwtManager;
        $this->jwtProvider = $jwtProvider;
        $this->userRepository = $userRepository;
    }
    public function checkToken(Request $request,$token=null)
    {
        if ($request->headers->has('Authorization')) {
            $data = explode(" ", $request->headers->get('Authorization'));
            if (count($data) == 2) {
                $token = $data[1];
                try {
                    $dataToken = $this->jwtProvider->load($token);
                    if ($dataToken-> isVerified()) {
                        $user = $this->userRepository->findOneBy(["email" => $dataToken->getPayload()["username"]]);
                        return ($user) ? $user : false;
                    }
                } catch (\Throwable $th) {
                    return false;
                }
            }
        }elseif($token){
            $dataToken = $this->jwtProvider->load($token);
            if( $dataToken->isVerified()) {
                $user = $this->userRepository->findOneBy(["email" => $dataToken->getPayload()["email"]]);
                return ($user) ? $user : false;
            }
            return false;
        }
        return false;
    }

    public function generateToken(string $email, int $exp){
        return $this->jwtProvider->create(["email" => $email, "exp" => $exp,"iat"=>time()])->getToken();
    }
    public function sendJsonErrorToken(): JsonResponse
    {
        return $this->json([
            'error' => true,
            'message' => "Authentification requise. Vous devez être connecté pour effectuer cette action."
        ],401);
    }
}




