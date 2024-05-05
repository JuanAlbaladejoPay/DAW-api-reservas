<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "Usuarios")]
class User implements UserInterface, PasswordAuthenticatedUserInterface {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 180, unique: true)]
  private ?string $email = null;

  #[ORM\Column]
  private array $roles = [];

  /**
   * @var string The hashed password
   */
  #[ORM\Column(nullable: true)]
  private ?string $password = null;

  #[ORM\Column(length: 255)]
  private ?string $nombre = null;

  #[ORM\Column(length: 255)]
  private ?string $apellidos = null;

  #[ORM\Column(nullable: true)]
  private ?int $telefono = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $picture = null;

  public function getId(): ?int {
    return $this->id;
  }

  public function getEmail(): ?string {
    return $this->email;
  }

  public function setEmail(string $email): static {
    $this->email = $email;

    return $this;
  }

  /**
   * A visual identifier that represents this user.
   *
   * @see UserInterface
   */
  public function getUserIdentifier(): string {
    return (string) $this->email;
  }

  /**
   * @see UserInterface
   */
  public function getRoles(): array {
    $roles = $this->roles;
    // guarantee every user at least has ROLE_USER
    $roles[] = 'ROLE_USER';

    return array_unique($roles);
  }

  public function setRoles(array $roles): static {
    $this->roles = $roles;

    return $this;
  }

  /**
   * @see PasswordAuthenticatedUserInterface
   */
  public function getPassword(): ?string {
    return $this->password;
  }

  public function setPassword(string $password): static {
    $this->password = $password;

    return $this;
  }

  /**
   * @see UserInterface
   */
  public function eraseCredentials(): void {
    // If you store any temporary, sensitive data on the user, clear it here
    // $this->plainPassword = null;
  }

  public function getNombre(): ?string {
    return $this->nombre;
  }

  public function setNombre(string $nombre): static {
    $this->nombre = $nombre;

    return $this;
  }

  public function getApellidos(): ?string {
    return $this->apellidos;
  }

  public function setApellidos(string $apellidos): static {
    $this->apellidos = $apellidos;

    return $this;
  }

  public function getTelefono(): ?int {
    return $this->telefono;
  }

  public function setTelefono(int $telefono): static {
    $this->telefono = $telefono;

    return $this;
  }

  public function getPicture(): ?string
  {
      return $this->picture;
  }

  public function setPicture(?string $picture): static
  {
      $this->picture = $picture;

      return $this;
  }
}
