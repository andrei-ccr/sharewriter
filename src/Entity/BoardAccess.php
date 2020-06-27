<?php

namespace App\Entity;

use App\Repository\BoardAccessRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BoardAccessRepository::class)
 */
class BoardAccess
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="boardAccesses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity=Board::class, inversedBy="boardAccesses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $board;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?User
    {
        return $this->email;
    }

    public function setEmail(?User $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getBoard(): ?Board
    {
        return $this->board;
    }

    public function setBoard(?Board $board): self
    {
        $this->board = $board;

        return $this;
    }
}
