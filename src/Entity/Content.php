<?php

namespace App\Entity;

use App\Repository\ContentRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: ContentRepository::class)]
#[Vich\Uploadable]
class Content
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['content.index', 'tuto.show', 'tutorial:admin'])]
    private ?int $id = null;

    #[ORM\Column(length: 6000, nullable: true)]
    #[Groups(['content.index', 'tuto.show', 'tutorial:admin'])]
    private ?string $text = null;

    #[ORM\Column(length: 6000, nullable: true)]
    #[Groups(['content.index', 'tuto.show', 'tutorial:admin'])]
    private ?string $code = null;

    #[ORM\Column]
    #[Groups(['content.index', 'tuto.show', 'tutorial:admin'])]
    private ?string $position = null;

    #[ORM\Column(length: 1000, nullable: true)]
    #[Groups(['content.index', 'tuto.show', 'tutorial:admin'])]
    private ?string $image = null;

    #[ORM\Column(length: 1000, nullable: true)]
    #[Groups(['content.index', 'tuto.show', 'tutorial:admin'])]
    private ?string $video = null;

    #[ORM\ManyToOne(inversedBy: 'contents')]
    private ?Chapter $Chapter = null;


     #[Vich\UploadableField(mapping:"content_image", fileNameProperty:"image")]
    private ?File $imageFile = null;

    #[ORM\Column(type:"datetime", nullable:true)]
    private ?DateTime $updatedAt = null;

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

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
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

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    #[Groups(['content.index', 'tuto.show', 'tutorial:admin'])]
    public function getImageUrl(): ?string
    {
        return $this->image ? '/images/content/' . $this->image : null;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }
}
