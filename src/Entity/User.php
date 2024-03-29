<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Un compte existe déjà avec cette adresse e-mail')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups("comment:read")]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'L\'email ne doit pas être vide')]
    #[Groups("comment:read")]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups("comment:read")]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le mot de passe ne doit pas être vide')]
    #[Assert\Length(min: 3, minMessage: 'Minimum {{ limit }} caractères')]
    #[Groups("comment:read")]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le pseudonyme ne doit pas être vide')]
    #[Assert\Length(min: 3, minMessage: 'Minimum {{ limit }} caractères')]
    #[Groups("comment:read")]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom ne doit pas être vide')]
    #[Assert\Length(min: 3, minMessage: 'Minimum {{ limit }} caractères')]
    #[Groups("comment:read")]
    private ?string $first_name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le prénom ne doit pas être vide')]
    #[Assert\Length(min: 3, minMessage: 'Minimum {{ limit }} caractères')]
    #[Groups("comment:read")]
    private ?string $last_name = null;

    #[ORM\Column(length: 255)]
    #[Groups("comment:read")]
    private ?string $avatar = null;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Trick::class)]
    #[Groups("comment:read")]
    private Collection $tricks;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Comment::class)]
    private Collection $comments;

    #[ORM\Column]
    private ?bool $login_is_verified = false;

    #[ORM\Column(length: 255, nullable: true)]
    private $login_token;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?DateTime $login_token_expires_at = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $password_token;

    public function __construct()
    {
        $this->tricks = new ArrayCollection();
        $this->comments = new ArrayCollection();
        // $this->roles = ['ROLE_USER'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return void
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): self
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): self
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * @return Collection<int, Trick>
     */
    public function getTricks(): Collection
    {
        return $this->tricks;
    }

    public function addTrick(Trick $trick): self
    {
        if (!$this->tricks->contains($trick)) {
            $this->tricks->add($trick);
            $trick->setAuthor($this);
        }

        return $this;
    }

    public function removeTrick(Trick $trick): self
    {
        if ($this->tricks->removeElement($trick)) {
            // set the owning side to null (unless already changed)
            if ($trick->getAuthor() === $this) {
                $trick->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

    public function getLoginIsVerified(): ?bool
    {
        return $this->login_is_verified;
    }

    public function setLoginIsVerified(bool $login_is_verified): self
    {
        $this->login_is_verified = $login_is_verified;

        return $this;
    }

    /**
     * Get the value of login_token
     */
    public function getLoginToken()
    {
        return $this->login_token;
    }

    /**
     * Set the value of login_token
     *
     * @return  self
     */
    public function setLoginToken($login_token)
    {
        $this->login_token = $login_token;

        return $this;
    }

    /**
     * Get the value of login_token_expires_at
     */
    public function getLoginTokenExpiresAt()
    {
        return $this->login_token_expires_at;
    }

    /**
     * Set the value of login_token_expires_at
     *
     * @return  self
     */
    public function setLoginTokenExpiresAt($login_token_expires_at)
    {
        $this->login_token_expires_at = $login_token_expires_at;

        return $this;
    }

    /**
     * Get the value of password_token
     */
    public function getPasswordToken()
    {
        return $this->password_token;
    }

    /**
     * Set the value of password_token
     *
     * @return  self
     */
    public function setPasswordToken($password_token)
    {
        $this->password_token = $password_token;

        return $this;
    }
}
