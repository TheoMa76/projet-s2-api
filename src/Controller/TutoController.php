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
    #[Route('/upload-image/{id}', name:"upload_tuto_image", methods:["POST"])]
    #[IsGranted("PUBLIC_ACCESS")]
    public function uploadImage(Request $request, $id): Response
    {
        $tuto = $this->entityManager->find(Tuto::class,$id);
        $file = $request->files->get('image');
        if ($file) {
            $tuto->setImageFile($file);
            $this->entityManager->persist($tuto);
            $this->entityManager->flush();

            return $this->json(['message' => 'Image uploaded successfully.'], Response::HTTP_OK);
        }

        return $this->json(['message' => 'No image uploaded.'], Response::HTTP_BAD_REQUEST);
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
