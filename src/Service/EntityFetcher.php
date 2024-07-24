<?php

namespace App\Service;

use App\Entity\Chapter;
use App\Entity\Content;
use App\Entity\Tuto;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Column;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

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

    public function getAll(): object|array
    {
        $repository = $this->entityManager->getRepository($this->entityClass);
        $entities = $repository->findAll();

        return $entities;
    }

    /**
     * @return object|array
     */
    public function find($id) 
    {
        $repository = $this->entityManager->getRepository($this->entityClass);
        $entity = $repository->findCustom($id);

        if (!$entity) {
            $entity = $repository->find($id);
            if(!$entity){
                return null;
            }
        }
            return $entity;
    }

    /**
     * @return object|array
     */
    public function findBy(array $criteria,$convertToArray = true): array
    {
        $repository = $this->entityManager->getRepository($this->entityClass);
        $entities = $repository->findBy($criteria);

        return $entities;
    }

    public function create(array $data,SerializerInterface $serializer,Request $request): object
    {
        $entity = new $this->entityClass();
        $entity = $this->checkRequirement($entity, $data);
        $entity = $serializer->deserialize($request->getContent(), $this->entityClass, 'json');
    

        // Persister et retourner l'entité
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }


    public function update($id, array $data, SerializerInterface $serializer, Request $request)
    {
        $entity = $this->find($id);

        if (!$entity) {
            throw new \Exception("Entity not found.");
        }

        $data = json_decode($request->getContent(), true);

        // Deserialize Tuto entity without chapters
        $entity = $serializer->deserialize($request->getContent(), $this->entityClass, 'json', [
            'object_to_populate' => $entity,
            'ignored_attributes' => ['chapters']
        ]);

        //TODO LATER FIND A WAY TO MAKE THIS GENERIC FOR ALL ENTITIES
        // Handle chapters separately
        if (isset($data['chapters'])) {
            $chapters = $data['chapters'];
            foreach ($chapters as $chapterData) {
                $chapter = new Chapter();
                $chapter->setTitle($chapterData['title']);
                $chapter->setDescription($chapterData['description']);
                $chapter->setPosition($chapterData['position']);
                $chapter->setTuto($entity);

                if (isset($chapterData['contents'])) {
                    foreach ($chapterData['contents'] as $contentData) {
                        $content = new Content();
                        $content->setText($contentData['text']);
                        $content->setCode($contentData['code']);
                        $content->setPosition($contentData['position']);
                        $content->setChapter($chapter);

                        $chapter->addContent($content);
                    }
                }

                $entity->addChapter($chapter);
            }
        }

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
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
            case 'Doctrine\Common\Collections\Collection':
                if (is_array($value)) {
                    $value = new ArrayCollection($value);
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

    private function setData($entity, array $data) {
        $reflectionClass = new \ReflectionClass($entity);
        foreach ($data as $key => $value) {
            if ($reflectionClass->hasProperty($key)) {
                $property = $reflectionClass->getProperty($key);
                $property->setAccessible(true);
                if(is_array($value)){
                    $methodName = 'add' . ucfirst($key);
                    if (method_exists($entity, $methodName)) {
                        $entity->$methodName($value);
                    }
                }else{
                    $property->setValue($entity, $value);
                }
            }
        }
    }
}
