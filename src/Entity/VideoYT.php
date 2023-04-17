<?php

namespace App\Entity;

use App\Repository\VideoYTRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VideoYTRepository::class)]
class VideoYT
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $ytVideoId = null;

    #[ORM\OneToOne(inversedBy: 'videoYT')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Trick $trick = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getYtVideoId(): ?string
    {
        return $this->ytVideoId;
    }

    public function setYtVideoId(string $ytVideoId): self
    {
        $this->ytVideoId = $ytVideoId;

        return $this;
    }

    public function getTrick(): ?Trick
    {
        return $this->trick;
    }

    public function setTrick(Trick $trick): self
    {
        $this->trick = $trick;

        return $this;
    }
}
