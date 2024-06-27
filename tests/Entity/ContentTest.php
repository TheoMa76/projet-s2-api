<?php

namespace App\Tests\Entity;

use App\Entity\Content;
use App\Entity\Chapter;
use PHPUnit\Framework\TestCase;

class ContentTest extends TestCase
{
    public function testGetId()
    {
        $content = new Content();
        $this->assertNull($content->getId());
    }

    public function testGetSetText()
    {
        $content = new Content();
        $text = 'This is a sample text.';
        $content->setText($text);
        $this->assertSame($text, $content->getText());
    }

    public function testGetSetCode()
    {
        $content = new Content();
        $code = '<p>Sample code</p>';
        $content->setCode($code);
        $this->assertSame($code, $content->getCode());
    }

    public function testGetSetPosition()
    {
        $content = new Content();
        $position = 1;
        $content->setPosition($position);
        $this->assertSame($position, $content->getPosition());
    }

    public function testGetSetImage()
    {
        $content = new Content();
        $image = 'image.png';
        $content->setImage($image);
        $this->assertSame($image, $content->getImage());
    }

    public function testGetSetVideo()
    {
        $content = new Content();
        $video = 'video.mp4';
        $content->setVideo($video);
        $this->assertSame($video, $content->getVideo());
    }

    public function testGetSetChapter()
    {
        $content = new Content();
        $chapter = $this->createMock(Chapter::class);
        $content->setChapter($chapter);
        $this->assertSame($chapter, $content->getChapter());
    }
}
