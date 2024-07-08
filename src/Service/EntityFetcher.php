<?php

namespace App\Service;

use App\Entity\Chapter;
use App\Entity\Content;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Column;
use Exception;
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
        // Persister et retourner l'entité
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
        $data = $this->checkPropertyTypes($entity, $data);
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
                if(!is_array($value)){
                    if ($expectedType && !$this->isTypeValid($expectedType, $value)) {
                        $typeErrors[] = $propertyName . ' expected type ' . $expectedType . ', but got ' . gettype($value);
                    }
                }else{
                    if ($expectedType && !$this->isTypeValid($expectedType, $value, $propertyName,$entity)) {
                        $typeErrors[] = $propertyName . ' expected type ' . $expectedType . ', but got ' . gettype($value);
                    }
                }
                $data[$propertyName] = $value;
            }
        }
        if(!empty($typeErrors)){
            trigger_error('Invalid Type : ' . implode(', ', $typeErrors), E_USER_ERROR);
        }else{
            return $data;
        }
    }

    private function isTypeValid(\ReflectionType $expectedType, &$value,$keyIfArray = null,$entity = null): bool
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
            case 'Doctrine\Common\Collections\Collection':
                if (is_array($value)) {
                    $value = new ArrayCollection($value);
                    $i = 0;
                    foreach ($value as $objectProperties) {
                        $className = 'App\\Entity\\' . ucfirst(rtrim($keyIfArray, 's'));
                
                        if (!class_exists($className)) {
                            throw new Exception("Class $className does not exist");
                        }
                
                        $obj = new $className();
                
                        foreach ($objectProperties as $property => $setValue) {
                            $potentialClassName = 'App\\Entity\\' . ucfirst(rtrim($property, 's'));
                
                            if (is_array($setValue) && class_exists($potentialClassName)) {
                                foreach($setValue as $key => $val){
                                    $subEntity = $this->createSubEntity($potentialClassName, $val);
                                    $this->addSubEntityToObject($obj, $property, $subEntity);
                                }
                            } else {
                                $this->setPropertyOnObject($obj, $property, $setValue);
                            }
                        }
                
                        $this->addEntityToCollection($entity, $keyIfArray, $obj);
                        $value->remove($i);
                        $value[$i] = $obj;
                        $i++;
                    }
                
                    return true;
                } elseif ($value instanceof ArrayCollection) {
                    return true;
                }
                break;
            default:
                if ($value instanceof $typeName) {
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * Create a sub-entity and set its properties.
     *
     * @param string $className
     * @param array $properties
     * @return object
     */
    function createSubEntity(string $className, array $properties) {
        $subEntity = new $className();
        foreach ($properties as $property => $values) {
            $methodName = 'set' . ucfirst($property);

            if (method_exists($subEntity, $methodName)) {
                $subEntity->$methodName($values);
            }
        }
        return $subEntity;
    }

    /**
     * Add a sub-entity to the main object.
     *
     * @param object $obj
     * @param string $property
     * @param object $subEntity
     */
    function addSubEntityToObject(object $obj, string $property, object $subEntity) {
        $methodName = 'add' . ucfirst(rtrim($property, 's'));

        if (method_exists($obj, $methodName)) {
            $obj->$methodName($subEntity);
        }
    }

    /**
     * Set a property on the main object.
     *
     * @param object $obj
     * @param string $property
     * @param mixed $value
     */
    function setPropertyOnObject(object $obj, string $property, $value) {
        $methodName = 'set' . ucfirst($property);

        if (method_exists($obj, $methodName)) {
            $obj->$methodName($value);
        }
    }

    /**
     * Add the main object to the collection.
     *
     * @param object $entity
     * @param string $keyIfArray
     * @param object $obj
     */
    function addEntityToCollection(object $entity, string $keyIfArray, object $obj) {
        $methodName = 'add' . ucfirst(rtrim($keyIfArray, 's'));

        if (method_exists($entity, $methodName)) {
            $entity->$methodName($obj);
        }
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
