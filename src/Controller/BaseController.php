<?php

namespace App\Controller;

use App\Entity\Tuto;
use App\Service\EntityFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

abstract class BaseController extends AbstractController
{
    protected $entityFetcher;
    protected $entityClass;

    public function __construct(EntityFetcher $entityFetcher)
    {
        $this->entityFetcher = $entityFetcher;
    }

    #[Route('/', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $data = $this->entityFetcher->getAll($this->entityClass);
        return new JsonResponse($data);
    }

    #[Route("/find/{id}", methods: ["GET"])]
    public function find($id): JsonResponse
    {
        $data = $this->entityFetcher->find($this->entityClass, $id);
        if ($data === null) {
            return new JsonResponse(['error' => 'Entity not found'], 404);
        }
        return new JsonResponse($data);
    }

    #[Route("/create", methods: ["POST"])]
    public function create(Request $request): JsonResponse
    {
        //dd($request->request->all());
        foreach ($request->request->all() as $key => $value) {
            if ($value === '' || $value === null) {
                return new JsonResponse(['error' => 'Missing field: ' . $key], 400);
            }
        }

        $data = $this->entityFetcher->create($this->entityClass, $request->request->all());

        $response = [
            'message' => 'Entity created',
            'data' => $data
        ];
        return new JsonResponse($response, 201);
    }

    #[Route("/update/{id}", methods: ["PUT"])]
    public function update($id, Request $request): JsonResponse
    {
        return new JsonResponse(['error' => 'Not implemented'], 501);

        // Logique de mise à jour (à compléter)
    }

    #[Route("/delete/{id}", methods: ["DELETE"])]
    public function delete($id): JsonResponse
    {
        return new JsonResponse(['error' => 'Not implemented'], 501);

        // Logique de suppression (à compléter)
    }
}
