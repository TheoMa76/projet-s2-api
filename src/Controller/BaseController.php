<?php

namespace App\Controller;

use App\Service\EntityFetcher;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\SerializerInterface;

abstract class BaseController extends AbstractController
{
    protected $entityFetcher;
    protected $entityClass;
    protected $groupnameIndex; 
    protected $groupnameShow;



    public function __construct(EntityFetcher $entityFetcher, string $entityClass)
    {
        $this->entityFetcher = $entityFetcher;
        $entityFetcher->setEntityClass($this->entityClass);
        $this->entityClass = $entityClass;
        $this->groupnameIndex = basename(strtolower(str_replace('\\', '/',$this->entityClass))) . '.index';
        $this->groupnameShow = basename(strtolower(str_replace('\\', '/',$this->entityClass))) . '.show';
    }

    #[Route('/', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $data = $this->entityFetcher->getAll($this->entityClass);
        $groupname = basename(strtolower(str_replace('\\', '/',$this->entityClass))) . '.index';
        return $this->json($data, 200, [], ['groups' => $groupname]);
    }

    #[Route("/find/{id}", methods: ["GET"],requirements: ["id" => Requirement::DIGITS])]
    public function find($id): JsonResponse
    {
        $data = $this->entityFetcher->find($id);
        if ($data === null) {
            return new JsonResponse(['error' => 'Entity not found'], 404);
        }

        return $this->json($data, 200, [], ['groups' => [$this->groupnameIndex,$this->groupnameShow]]);
    }

    #[Route("/create", methods: ["POST"])]
    public function create(Request $request, SerializerInterface $serializer): JsonResponse
    {   
        // Récupération des données envoyées dans la requête
        $data = $this->entityFetcher->create(json_decode($request->getContent(), true), $serializer, $request);

        $dataResp = [
            'message' => 'Entity created',
        ];
        $response = array_merge($dataResp, ['data' => $data]);

        return $this->json($response, 200, [], ['groups' => [$this->groupnameIndex, $this->groupnameShow]]);
    }


    #[Route("/update/{id}", methods: ["PUT"])]
    public function update(Request $request,$id, SerializerInterface $serializer): JsonResponse
    {
        $data = $this->entityFetcher->update($id,json_decode($request->getContent(), true),$serializer, $request);

        $dataResp = [
            'message' => 'Entity updated',
            'idUpdated' => $id,
        ];
        $response = array_merge($dataResp, ['data' => $data]);

        return $this->json($response, 200, [], ['groups' => [$this->groupnameIndex, $this->groupnameShow]]);

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
