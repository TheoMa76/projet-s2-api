<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;

class EntityFetcher
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getAll(string $entityClass): array
    {
        $repository = $this->entityManager->getRepository($entityClass);
        $entities = $repository->findAll();

        $data = [];
        foreach ($entities as $entity) {
            $data[] = $this->entityToArray($entity);
        }

        return $data;
    }

    public function find(string $entityClass, $id): ?array
    {
        $repository = $this->entityManager->getRepository($entityClass);
        $entity = $repository->find($id);

        if (!$entity) {
            return null;
        }

        return $this->entityToArray($entity);
    }

    private function entityToArray($entity): array
    {
        $reflectionClass = new ReflectionClass($entity);
        $properties = $reflectionClass->getProperties();
        $data = [];

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $data[$property->getName()] = $property->getValue($entity);
        }

        return $data;
    }
}
