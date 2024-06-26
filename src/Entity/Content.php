<?php

namespace App\Entity;

use App\Repository\ContentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContentRepository::class)]
class Content
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $text = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $code = null;

    #[ORM\Column]
    private ?int $position = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $video = null;

    #[ORM\ManyToOne(inversedBy: 'contents')]
    private ?Chapter $Chapter = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideo(?string $video): static
    {
        $this->video = $video;

        return $this;
    }

    public function getChapter(): ?Chapter
    {
        return $this->Chapter;
    }

    public function setChapter(?Chapter $Chapter): static
    {
        $this->Chapter = $Chapter;

        return $this;
    }
}
