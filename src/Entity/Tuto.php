<?php

namespace App\Entity;

use App\Repository\TutoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: TutoRepository::class)]
#[Vich\Uploadable]
class Tuto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['tuto.index', 'tutorial:admin'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['tuto.index', 'tutorial:admin', 'tuto.preview'])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['tuto.index', 'tutorial:admin', 'tuto.preview'])]
    private ?string $estimated_time = null;

    #[ORM\Column(length: 255)]
    #[Groups(['tuto.index', 'tutorial:admin', 'tuto.preview'])]
    private ?string $difficulty = null;

    #[ORM\Column(length: 255)]
    #[Groups(['tuto.index', 'tutorial:admin', 'tuto.preview'])]
    private ?string $game = null;

    /**
     * @var Collection<int, Chapter>
     */
    #[ORM\OneToMany(targetEntity: Chapter::class, mappedBy: 'Tuto', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['tuto.show', 'tutorial:admin', 'tuto.preview'])]
    private Collection $chapters;

    #[ORM\Column]
    #[Groups(['tuto.index', 'tutorial:admin', 'tuto.preview'])]
    private ?string $position = null;

    #[Vich\UploadableField(mapping:"tuto_image", fileNameProperty:"image")]
    private ?File $imageFile = null;

     #[ORM\Column(type:"string", length:255, nullable:true)]
     #[Groups(['tuto.index', 'tutorial:admin', 'tuto.preview','tuto.show'])]
    private ?string $image = null;

    #[ORM\Column(nullable:true)]
    #[Groups(['tuto.index', 'tutorial:admin', 'tuto.preview'])]
    private ?\DateTime $updatedAt = null;

    public function __construct()
    {
        $this->chapters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getEstimatedTime(): ?string
    {
        return $this->estimated_time;
    }

    public function setEstimatedTime(string $estimated_time): static
    {
        $this->estimated_time = $estimated_time;

        return $this;
    }

    public function getDifficulty(): ?string
    {
        return $this->difficulty;
    }

    public function setDifficulty(string $difficulty): static
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getGame(): ?string
    {
        return $this->game;
    }

    public function setGame(string $game): static
    {
        $this->game = $game;

        return $this;
    }

    /**
     * @return Collection<int, Chapter>
     */
    public function getChapters(): Collection
    {
        return $this->chapters;
    }

    public function addChapter(Chapter $chapter): static
    {
        if (!$this->chapters->contains($chapter)) {
            $this->chapters->add($chapter);
            $chapter->setTuto($this);
        }

        return $this;
    }

    public function removeChapter(Chapter $chapter): static
    {
        if ($this->chapters->removeElement($chapter)) {
            // set the owning side to null (unless already changed)
            if ($chapter->getTuto() === $this) {
                $chapter->setTuto(null);
            }
        }

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

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            $this->updatedAt = new \DateTime();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    #[Groups(['tuto.index', 'tutorial:admin', 'tuto.preview','tuto.show'])]
    public function getImageUrl(): ?string
    {
        return $this->image ? '/images/tuto/' . $this->image : null;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
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
