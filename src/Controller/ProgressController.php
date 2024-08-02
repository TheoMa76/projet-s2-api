<?php

namespace App\Controller;

use App\Entity\Progress;
use App\Repository\ChapterRepository;
use App\Repository\UserRepository;
use App\Service\EntityFetcher;
use App\Service\JWTDecoderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

#[Route("/progress")]
class ProgressController extends BaseController
{
    protected $entityClass = Progress::class;
    private $entityManager = EntityManagerInterface::class;
    private $userRepository;
    private $chapterRepository;
    private $jwtDecoder;



    public function __construct(JWTDecoderService $jwtDecoder,EntityFetcher $entityFetcher,EntityManagerInterface $entityManager, UserRepository $userRepository, ChapterRepository $chapterRepository)
    {
        parent::__construct($entityFetcher, $this->entityClass);
        $this->userRepository = $userRepository;
        $this->chapterRepository = $chapterRepository;
        $this->entityManager = $entityManager;
        $this->jwtDecoder = $jwtDecoder;
    }

    #[Route("/progression/create", methods: ["POST"])]
    public function createProgress(Request $request, SerializerInterface $serializer): JsonResponse
    {

        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return new JsonResponse(['error' => 'Token not provided'], 401);
        }

        $token = $matches[1];

        $data = json_decode($request->getContent(), true);
        $token = $this->jwtDecoder->decode($token);
            $email = $token['username'];
            $user = $this->userRepository->findWithProgress($email);

        $chapter = $this->chapterRepository->find(['id' => $data['chapter']]);
        $entity = $serializer->deserialize($request->getContent(), Progress::class, 'json');
        $entity->setUser($user);
        $entity->setChapter($chapter);
        $entity->setCompletedAt(new \DateTime($data['completedAt']));
        $entity->setCompleted($data['isCompleted']);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $this->json($data, 200, [], ['groups' => 'progress.index']);
    }

}
