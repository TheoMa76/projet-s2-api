<?php

namespace App\Tests\Entity;

use App\Entity\Tuto;
use App\Entity\Chapter;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertIsObject;

class TutoTest extends TestCase
{
    public function testGetId()
    {
        $tuto = new Tuto();
        $this->assertNull($tuto->getId());
    }

    public function testGetSetTitle()
    {
        $tuto = new Tuto();
        $tuto->setTitle('Test Title');
        $this->assertSame('Test Title', $tuto->getTitle());
    }

    public function testGetSetEstimatedTime()
    {
        $tuto = new Tuto();
        $tuto->setEstimatedTime('2 hours');
        $this->assertSame('2 hours', $tuto->getEstimatedTime());
    }

    public function testGetSetDifficulty()
    {
        $tuto = new Tuto();
        $tuto->setDifficulty('Easy');
        $this->assertSame('Easy', $tuto->getDifficulty());
    }

    public function testGetSetGame()
    {
        $tuto = new Tuto();
        $tuto->setGame('Game Name');
        $this->assertSame('Game Name', $tuto->getGame());
    }

    public function testGetSetPosition()
    {
        $tuto = new Tuto();
        $tuto->setPosition(1);
        $this->assertSame(1, $tuto->getPosition());
    }

    public function testGetAddChapters()
    {
        $tuto = new Tuto();
        $chapter = $this->createMock(Chapter::class);

        // Test initial state
        $this->assertInstanceOf(Collection::class, $tuto->getChapters());
        $this->assertCount(0, $tuto->getChapters());

        // Test addChapter
        $chapter->expects($this->once())
                ->method('setTuto')
                ->with($tuto);

        $tuto->addChapter($chapter);
        $this->assertCount(1, $tuto->getChapters());
        $this->assertTrue($tuto->getChapters()->contains($chapter));
    }

    public function testRemoveChapters()
    {
        $tuto = new Tuto();
        $chapter = $this->createMock(Chapter::class);

        // Adding chapter first


        $tuto->addChapter($chapter);
        $this->assertCount(1, $tuto->getChapters());
        $this->assertTrue($tuto->getChapters()->contains($chapter));
        $chapter->expects($this->once())
                ->method('getTuto')
                ->willReturn($tuto);

        // Now remove the chapter
        $tuto->removeChapter($chapter);
        $this->assertCount(0, $tuto->getChapters());
        $this->assertFalse($tuto->getChapters()->contains($chapter));
    }
}
