<?php

namespace App\Entity;

use App\Repository\VideoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
class Video
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Url(message: "L'URL '{{ value }}' n'est pas une URL valide.")]
    private $url;

    #[ORM\ManyToOne(targetEntity: Trick::class, inversedBy: 'videos')]
    #[ORM\JoinColumn(nullable: false)]
    private $trick;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getTrick(): ?Trick
    {
        return $this->trick;
    }

    public function setTrick(?Trick $trick): self
    {
        $this->trick = $trick;

        return $this;
    }

    public function getEmbedUrl(): string
    {
        // Transforme l'identifiant de vidéo en URL d'incorporation utilisée dans l'iframe
        // Exemple: l69YDfVU9qs devient https://www.youtube.com/embed/l69YDfVU9qs
        if (!empty($this->url)) {
            $start = strpos($this->url, 'v=') + 2;
            $videoId = substr($this->url, $start);
            return 'https://www.youtube.com/embed/' . $videoId;
        }

        return '';
    }
}
