<?php

namespace App\Tests\Entity;

use App\Entity\Chapter;
use App\Entity\Content;
use App\Entity\Tuto;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class ChapterTest extends TestCase
{
    public function testGetId()
    {
        $chapter = new Chapter();
        $this->assertNull($chapter->getId());
    }

    public function testGetSetTitle()
    {
        $chapter = new Chapter();
        $title = 'Chapter Title';
        $chapter->setTitle($title);
        $this->assertSame($title, $chapter->getTitle());
    }

    public function testGetSetDescription()
    {
        $chapter = new Chapter();
        $description = 'Chapter Description';
        $chapter->setDescription($description);
        $this->assertSame($description, $chapter->getDescription());
    }

    public function testGetSetTuto()
    {
        $chapter = new Chapter();
        $tuto = $this->createMock(Tuto::class);
        $chapter->setTuto($tuto);
        $this->assertSame($tuto, $chapter->getTuto());
    }

    public function testGetSetPosition()
    {
        $chapter = new Chapter();
        $position = 1;
        $chapter->setPosition($position);
        $this->assertSame($position, $chapter->getPosition());
    }

    public function testGetContents()
    {
        $chapter = new Chapter();
        $this->assertInstanceOf(Collection::class, $chapter->getContents());
        $this->assertCount(0, $chapter->getContents());
    }

    public function testAddContent()
    {
        $chapter = new Chapter();
        $content = $this->createMock(Content::class);

        $content->expects($this->once())
                ->method('setChapter')
                ->with($chapter);

        $chapter->addContent($content);
        $this->assertCount(1, $chapter->getContents());
        $this->assertTrue($chapter->getContents()->contains($content));
    }

    public function testRemoveContent()
    {
        $chapter = new Chapter();
        $content = $this->createMock(Content::class);

        $content->setChapter($chapter);

        $chapter->addContent($content);

        $content->expects($this->once())
                ->method('getChapter')
                ->willReturn($chapter);

        $chapter->removeContent($content);
        $this->assertCount(0, $chapter->getContents());
        $this->assertFalse($chapter->getContents()->contains($content));
    }
}
