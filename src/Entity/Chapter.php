<?php

namespace App\Entity;

use App\Repository\ChapterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ChapterRepository::class)]
class Chapter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['chapter.index','tuto.show','progress.index','tutorial:admin'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['chapter.index','tuto.show','progress.index','tutorial:admin'])]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['chapter.index','tuto.show','tutorial:admin'])]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'chapters')]
    private ?Tuto $Tuto = null;

    #[ORM\Column]
    #[Groups(['chapter.index','tuto.show','tutorial:admin'])]
    private ?int $position = null;

    /**
     * @var Collection<int, Content>
     */
    #[ORM\OneToMany(targetEntity: Content::class, mappedBy: 'Chapter',cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['tuto.show','tutorial:admin'])]
    private Collection $contents;

    /**
     * @var Collection<int, Progress>
     */
    #[ORM\OneToMany(targetEntity: Progress::class, mappedBy: 'chapter')]
    private Collection $progress;

    public function __construct()
    {
        $this->contents = new ArrayCollection();
        $this->progress = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getTuto(): ?Tuto
    {
        return $this->Tuto;
    }

    public function setTuto(?Tuto $Tuto): static
    {
        $this->Tuto = $Tuto;

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

    /**
     * @return Collection<int, Content>
     */
    public function getContents(): Collection
    {
        return $this->contents;
    }

    public function addContent(Content $content): static
    {
        if (!$this->contents->contains($content)) {
            $this->contents->add($content);
            $content->setChapter($this);
        }

        return $this;
    }

    public function removeContent(Content $content): static
    {
        if ($this->contents->removeElement($content)) {
            // set the owning side to null (unless already changed)
            if ($content->getChapter() === $this) {
                $content->setChapter(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Progress>
     */
    public function getProgress(): Collection
    {
        return $this->progress;
    }

    public function addProgress(Progress $progress): static
    {
        if (!$this->progress->contains($progress)) {
            $this->progress->add($progress);
            $progress->setChapter($this);
        }

        return $this;
    }

    public function removeProgress(Progress $progress): static
    {
        if ($this->progress->removeElement($progress)) {
            // set the owning side to null (unless already changed)
            if ($progress->getChapter() === $this) {
                $progress->setChapter(null);
            }
        }

        return $this;
    }
}
