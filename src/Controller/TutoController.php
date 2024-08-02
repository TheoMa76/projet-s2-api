<?php

namespace App\Controller;

use App\Entity\Tuto;
use App\Service\EntityFetcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

//TODO RAJOUTER UNE IMAGE DE PRESENTATION ET UNE DESCRIPTION

#[Route("/tuto")]
class TutoController extends BaseController
{
    protected $entityClass = Tuto::class;
    private $entityManager;

    public function __construct(EntityFetcher $entityFetcher,EntityManagerInterface $entityManager)
    {
        parent::__construct($entityFetcher, $this->entityClass);
        $this->entityManager = $entityManager;
    }

    #[Route('/preview/find/{id}', methods: ['GET'])]
    #[IsGranted("PUBLIC_ACCESS")]
    public function preview(int $id): Response
    {
        $data = $this->entityFetcher->find($id);
        if ($data === null) {
            return new JsonResponse(['error' => 'Entity not found'], 404);
        }

        return $this->json($data, 200, [], ['groups' => ['tuto.preview']]);
    }

}
