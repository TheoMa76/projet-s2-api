<?php

namespace App\Controller;

use App\Entity\Progress;
use App\Repository\ChapterRepository;
use App\Repository\UserRepository;
use App\Service\EntityFetcher;
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


    public function __construct(EntityFetcher $entityFetcher,EntityManagerInterface $entityManager, UserRepository $userRepository, ChapterRepository $chapterRepository)
    {
        parent::__construct($entityFetcher, $this->entityClass);
        $this->userRepository = $userRepository;
        $this->chapterRepository = $chapterRepository;
        $this->entityManager = $entityManager;
    }

    #[Route("/test/create", methods: ["POST"])]
    public function createProgress(Request $request, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->userRepository->find(['id' => $data['user']]);
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
