<?php

namespace App\Tests\Service;

use App\Entity\Tuto;
use App\Service\EntityFetcher;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use stdClass;

class EntityFetcherTest extends TestCase
{
    private $entityManager;
    private $entityFetcher;
    private $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(EntityRepository::class);
        $this->entityManager->method('getRepository')->willReturn($this->repository);

        $this->entityFetcher = new EntityFetcher($this->entityManager);
        $this->entityFetcher->setEntityClass(stdClass::class);
    }

    public function testGetAll()
    {
        $entity = new stdClass();
        $entity->id = 1;
        $entity->name = 'Test Entity';

        $this->repository->method('findAll')->willReturn([$entity]);

        $result = $this->entityFetcher->getAll();

        $expected = [
            ['id' => 1, 'name' => 'Test Entity']
        ];

        $this->assertEquals($expected, $result);
        $tuto = new Tuto();
        $tuto->setTitle('Test Title');
        $tuto->setEstimatedTime('2 hours');
        $tuto->setDifficulty('Easy');
        $tuto->setGame('Game Name');
        $tuto->setPosition(1);

        $this->repository->method('findAll')->willReturn([$tuto]);

        $result = $this->entityFetcher->getAll();

        $expected = [
            [
                'id' => null,
                'title' => 'Test Title',
                'estimatedTime' => '2 hours',
                'difficulty' => 'Easy',
                'game' => 'Game Name',
                'position' => 1,
                'chapters' => []
            ]
        ];

        $this->assertEquals($expected, $result);


    }

    public function testFind()
    {
        $entity = new stdClass();
        $entity->id = 1;
        $entity->name = 'Test Entity';

        $this->repository->method('find')->with(1)->willReturn($entity);

        $result = $this->entityFetcher->find(1);

        $expected = ['id' => 1, 'name' => 'Test Entity'];

        $this->assertEquals($expected, $result);
        $tuto = new Tuto();
        $tuto->setTitle('Test Title');
        $tuto->setEstimatedTime('2 hours');
        $tuto->setDifficulty('Easy');
        $tuto->setGame('Game Name');
        $tuto->setPosition(1);

        $this->repository->method('find')->with(1)->willReturn($tuto);

        $result = $this->entityFetcher->find(1);

        $expected = [
            'id' => null,
            'title' => 'Test Title',
            'estimatedTime' => '2 hours',
            'difficulty' => 'Easy',
            'game' => 'Game Name',
            'position' => 1,
            'chapters' => []
        ];

        $this->assertEquals($expected, $result);
    }

    public function testFindEntityNotFound()
    {
        $this->repository->method('find')->with(1)->willReturn(null);

        $result = $this->entityFetcher->find(1);

        $this->assertNull($result);
    }

    public function testCreate()
    {
        $data = ['id' => 1, 'name' => 'Test Entity'];

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $result = $this->entityFetcher->create($data);

        $this->assertEquals($data, $result);

        $data = [
            'title' => 'Test Title',
            'estimatedTime' => '2 hours',
            'difficulty' => 'Easy',
            'game' => 'Game Name',
            'position' => 1
        ];

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $result = $this->entityFetcher->create($data);

        $expected = $data;
        $expected['id'] = null;
        $expected['chapters'] = [];

        $this->assertEquals($expected, $result);
    }

    public function testUpdate()
    {
        $entity = new stdClass();
        $entity->id = 1;
        $entity->name = 'Old Name';

        $this->repository->method('find')->with(1)->willReturn($entity);

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $data = ['name' => 'New Name'];
        $result = $this->entityFetcher->update(1, $data);

        $expected = ['id' => 1, 'name' => 'New Name'];

        $this->assertEquals($expected, $result);
    }

    public function testDelete()
    {
        $entity = new stdClass();
        $entity->id = 1;

        $this->repository->method('find')->with(1)->willReturn($entity);

        $this->entityManager->expects($this->once())->method('remove')->with($entity);
        $this->entityManager->expects($this->once())->method('flush');

        $this->entityFetcher->delete(1);
    }
}
