<?php

namespace App\Entity;

use App\Repository\BoardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BoardRepository::class)
 */
class Board
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\Column(type="boolean")
     */
    private $private;

    /**
     * @ORM\OneToMany(targetEntity=BoardAccess::class, mappedBy="board", orphanRemoval=true)
     */
    private $boardAccesses;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="boards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    public function __construct()
    {
        $this->boardAccesses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getPrivate(): ?bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): self
    {
        $this->private = $private;

        return $this;
    }

    /**
     * @return Collection|BoardAccess[]
     */
    public function getBoardAccesses(): Collection
    {
        return $this->boardAccesses;
    }

    public function addBoardAccess(BoardAccess $boardAccess): self
    {
        if (!$this->boardAccesses->contains($boardAccess)) {
            $this->boardAccesses[] = $boardAccess;
            $boardAccess->setBoard($this);
        }

        return $this;
    }

    public function removeBoardAccess(BoardAccess $boardAccess): self
    {
        if ($this->boardAccesses->contains($boardAccess)) {
            $this->boardAccesses->removeElement($boardAccess);
            // set the owning side to null (unless already changed)
            if ($boardAccess->getBoard() === $this) {
                $boardAccess->setBoard(null);
            }
        }

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

}
