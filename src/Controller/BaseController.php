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

    public function __construct(EntityFetcher $entityFetcher, string $entityClass)
    {
        $this->entityFetcher = $entityFetcher;
        $entityFetcher->setEntityClass($this->entityClass);
        $this->entityClass = $entityClass;
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
        $data = $this->entityFetcher->find($id);
        if ($data === null) {
            return new JsonResponse(['error' => 'Entity not found'], 404);
        }
        return new JsonResponse($data);
    }

    #[Route("/create", methods: ["POST"])]
    public function create(Request $request): JsonResponse
    {   
        $data = $this->entityFetcher->create($request->request->all());

        $response = [
            'message' => 'Entity created',
            'data' => $data
        ];
        return new JsonResponse($response, 200);
    }

    #[Route("/update/{id}", methods: ["PUT"])]
    public function update(Request $request,$id): JsonResponse
    {
        $data = $this->entityFetcher->update($id,$request->request->all());
        $response = [
            'message' => 'Entity updated',
            'data' => $data
        ];
        return new JsonResponse($response, 200);

    }

    #[Route("/delete/{id}", methods: ["DELETE"])]
    public function delete($id): JsonResponse
    {
        $data = $this->entityFetcher->delete($id);
        $response = [
            'message' => 'Entity deleted',
            'idDeleted' => $id,
        ];
        return new JsonResponse($response, 200);
    }
}
