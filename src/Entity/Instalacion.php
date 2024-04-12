<?php

namespace App\Entity;

use App\Repository\InstalacionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InstalacionRepository::class)]
#[ORM\Table(name: "Instalaciones")]
class Instalacion {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $nombre = null;

  #[ORM\Column(name: "precioHora")]
  private ?float $precioHora = null;

  public function getId(): ?int {
    return $this->id;
  }

  public function getNombre(): ?string {
    return $this->nombre;
  }

  public function setNombre(string $nombre): static {
    $this->nombre = $nombre;

    return $this;
  }

  public function getPrecioHora(): ?float {
    return $this->precioHora;
  }

  public function setPrecioHora(float $precioHora): static {
    $this->precioHora = $precioHora;

    return $this;
  }
}
