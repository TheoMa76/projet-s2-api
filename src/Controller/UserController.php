<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\EntityFetcher;
use App\Service\JWTDecoderService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/user")]
class UserController extends BaseController
{
    protected $entityClass = User::class;
    private $jwtDecoder;
    private $userRepository;

    public function __construct(JWTDecoderService $jwtDecoder, UserRepository $userRepository,EntityFetcher $entityFetcher)
    {
        parent::__construct($entityFetcher, $this->entityClass);
        $this->jwtDecoder = $jwtDecoder;
        $this->userRepository = $userRepository;
    }

    #[Route('/connected', methods: ['GET'])]
    public function getConnectedUser(Request $request): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return new JsonResponse(['error' => 'Token not provided'], 401);
        }

        $token = $matches[1];

        try {
            $data = $this->jwtDecoder->decode($token);
            $email = $data['username'];
            $user = $this->userRepository->findOneBy(['email' => $email]);

            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], 404);
            }

            return new JsonResponse([
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'createdAt' => $user->getCreatedAt(),
                'updatedAt' => $user->getUpdatedAt(),
            ]);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => 'Invalid Token'], 401);
        }
    }
}
