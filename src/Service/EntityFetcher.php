<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Column;
use PhpParser\Builder\Class_;
use ReflectionClass;

class EntityFetcher
{
    private $entityManager;
    private $entityClass;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function setEntityClass(string $entityClass)
    {
        $this->entityClass = $entityClass;
    }

    public function getAll(): array
    {
        $repository = $this->entityManager->getRepository($this->entityClass);
        $entities = $repository->findAll();

        $data = [];
        foreach ($entities as $entity) {
            $data[] = $this->entityToArray($entity);
        }

        return $data;
    }

    /**
     * @return object|array
     */
    public function find($id, $convertToArray = true) 
    {
        $repository = $this->entityManager->getRepository($this->entityClass);
        $entity = $repository->find($id);

        if (!$entity) {
            return null;
        }

        if ($convertToArray) {
            return $this->entityToArray($entity);
        }else{
            return $entity;
        }
    }

    /**
     * @return object|array
     */
    public function findBy(array $criteria,$convertToArray = true): array
    {
        $repository = $this->entityManager->getRepository($this->entityClass);
        $entities = $repository->findBy($criteria);

        $data = [];
        foreach ($entities as $entity) {
            if ($convertToArray) {
                $data[] = $this->entityToArray($entity);
            }else{
                $data[] = $entity;
            }
        }

        return $data;
    }

    public function create(array $data): array
    {

        $entity = new $this->entityClass();
        $entity = $this->checkRequirement($entity, $data);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $this->entityToArray($entity);
    }

    public function update($id, array $data){
        $entity = $this->find($id,false);
        if (!$entity) {
            throw new \Exception("Entity not found.");
        }
        $entity = $this->checkRequirement($entity, $data);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $this->entityToArray($entity);
    }

    public function delete($id){
        $entity = $this->find($id,false);
        if (!$entity) {
            throw new \Exception("Entity not found.");
        }
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
    

    private function checkRequirement($entity,array $data,$addIgnoredProperties = []){
        $typeErrors = $this->checkPropertyTypes($entity, $data);
        if (!empty($typeErrors)) {
            trigger_error('Type mismatch: ' . implode(', ', $typeErrors), E_USER_ERROR);
        }
        $this->setData($entity, $data);

        $ignoredProperties = ['created_at', 'updated_at', 'id'];
        $ignoredProperties = array_merge($ignoredProperties, $addIgnoredProperties);

        $missingProperties = $this->getMissingProperties($entity, $ignoredProperties);
        if (!empty($missingProperties)) {
            $nullableProperties = $this->getNullableProperties($entity, $ignoredProperties);
            $missingNonNullableProperties = array_diff($missingProperties, $nullableProperties);
            if (!empty($missingNonNullableProperties)) {
                trigger_error('Missing non-nullable properties: ' . implode(', ', $missingNonNullableProperties), E_USER_ERROR);
            }
        }

        return $entity;
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

    private function checkPropertyTypes($entity, array $data): array
    {
        $reflectionClass = new ReflectionClass($entity);
        $properties = $reflectionClass->getProperties();
        $typeErrors = [];

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            if (array_key_exists($propertyName, $data)) {
                $property->setAccessible(true);
                $expectedType = $property->getType();
                $value = $data[$propertyName];

                if ($expectedType && !$this->isTypeValid($expectedType, $value)) {
                    $typeErrors[] = $propertyName . ' expected type ' . $expectedType . ', but got ' . gettype($value);
                }
            }
        }
        return $typeErrors;
    }

    private function isTypeValid(\ReflectionType $expectedType, &$value): bool
    {
        $typeName = $expectedType->getName();

        if ($expectedType->allowsNull() && $value === null) {
            return true;
        }

        switch ($typeName) {
            case 'int':
                if (is_int($value)) {
                    return true;
                } elseif (ctype_digit($value) && intval($value) == $value) {
                    $value = (int) $value;
                    return true;
                }
                break;

            case 'float':
                if (is_float($value)) {
                    return true;
                } elseif (is_numeric($value)) {
                    $value = (float) $value;
                    return true;
                }
                break;

            case 'string':
                if (is_string($value)) {
                    return true;
                } elseif (is_scalar($value)) {
                    $value = (string) $value;
                    return true;
                }
                break;

            case 'bool':
                if (is_bool($value)) {
                    return true;
                } elseif (in_array(strtolower($value), ['true', 'false', '1', '0'], true)) {
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    return $value !== null;
                }
                break;

            case 'array':
                return is_array($value);

            default:
                if ($value instanceof $typeName) {
                    return true;
                }
                break;
        }

        return false;
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

    private function setData($entity, array $data) {
        $reflectionClass = new \ReflectionClass($entity);
        foreach ($data as $key => $value) {
            if ($reflectionClass->hasProperty($key)) {
                $property = $reflectionClass->getProperty($key);
                $property->setAccessible(true);
                $property->setValue($entity, $value);
            }
        }
    }
    

}
