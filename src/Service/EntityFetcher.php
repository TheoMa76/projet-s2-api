<?php

namespace App\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Column;
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

    public function findBy(string $entityClass, array $criteria): array
    {
        $repository = $this->entityManager->getRepository($entityClass);
        $entities = $repository->findBy($criteria);

        $data = [];
        foreach ($entities as $entity) {
            $data[] = $this->entityToArray($entity);
        }

        return $data;
    }

    public function create(string $entityClass, array $data): array
    {
        $entity = new $entityClass();
        $this->setData($entity, $data);

        $ignoredProperties = ['created_at', 'updated_at', 'id'];

        $missingProperties = $this->getMissingProperties($entity, $ignoredProperties);
        if (!empty($missingProperties)) {
            $nullableProperties = $this->getNullableProperties($entity, $ignoredProperties);
            $missingNonNullableProperties = array_diff($missingProperties, $nullableProperties);
            if (!empty($missingNonNullableProperties)) {
                trigger_error('Missing non-nullable properties: ' . implode(', ', $missingNonNullableProperties), E_USER_ERROR);
            }
        }

        $reflectionClass = new ReflectionClass($entity);
        foreach ($ignoredProperties as $property) {
            if ($reflectionClass->hasProperty($property)) {
                $ignoredProperty = $reflectionClass->getProperty($property);
                $ignoredProperty->setAccessible(true);
                $ignoredProperty->setValue($entity, null);
            }
        }

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $this->entityToArray($entity);
    }

    private function getMissingProperties($entity, array $ignoredProperties = []): array
    {
        $reflectionClass = new ReflectionClass($entity);
        $properties = $reflectionClass->getProperties();
        $missingProperties = [];
        foreach ($properties as $property) {
            if (!in_array($property->getName(), $ignoredProperties)) {
                $property->setAccessible(true);
                $value = $property->getValue($entity);
                if ($value === null) {
                    $missingProperties[] = $property->getName();
                }
            }
        }
        return $missingProperties;
    }

    private function getNullableProperties($entity, array $ignoredProperties = []): array
    {
        $reflectionClass = new ReflectionClass($entity);
        $properties = $reflectionClass->getProperties();
        $nullableProperties = [];

        foreach ($properties as $property) {
            if (!in_array($property->getName(), $ignoredProperties)) {
                $property->setAccessible(true);

                // Lire les attributs de la propriété
                $attributes = $property->getAttributes(Column::class);
                foreach ($attributes as $attribute) {
                    $attributeInstance = $attribute->newInstance();
                    if (isset($attributeInstance->nullable) && $attributeInstance->nullable === true) {
                        $nullableProperties[] = $property->getName();
                    }
                }
            }
        }
        return $nullableProperties;
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

    private function setData($entity, array $data): void
    {
        foreach ($data as $key => $value) {
            $setter = 'set' . str_replace('_', '', ucwords($key, '_'));
            $entity->$setter($value);
        }
    }

}
