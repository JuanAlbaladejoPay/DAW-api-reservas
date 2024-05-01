<?php

namespace App\Entity;

use App\Repository\ReservaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservaRepository::class)]
#[ORM\Table(name: "Reservas")]
class Reserva {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column]
  private ?int $duracion = null;

  #[ORM\Column]
  private ?float $importe = null;

  #[ORM\ManyToOne(targetEntity: User::class)]
  #[ORM\JoinColumn(name: "idUsuario", referencedColumnName: "id", nullable: false)]
  private ?User $idUsuario = null;

  #[ORM\ManyToOne(targetEntity: Instalacion::class)]
  #[ORM\JoinColumn(name: "idInstalacion", referencedColumnName: "id", nullable: false)]
  private ?Instalacion $idInstalacion = null;

  #[ORM\Column(type: Types::DATETIME_MUTABLE, name: "fechaYHora")]
  private ?\DateTimeInterface $fechaYHora = null;


  public function getId(): ?int {
    return $this->id;
  }

  public function getDuracion(): ?int {
    return $this->duracion;
  }

  public function setDuracion(int $duracion): static {
    $this->duracion = $duracion;

    return $this;
  }

  public function getImporte(): ?float {
    return $this->importe;
  }

  public function setImporte(float $importe): static {
    $this->importe = $importe;

    return $this;
  }

  public function getIdUsuario(): ?User {
    return $this->idUsuario;
  }

  public function setIdUsuario(?User $idUsuario): static {
    $this->idUsuario = $idUsuario;

    return $this;
  }

  public function getIdInstalacion(): ?Instalacion {
    return $this->idInstalacion;
  }

  public function setIdInstalacion(?Instalacion $idInstalacion): static {
    $this->idInstalacion = $idInstalacion;

    return $this;
  }

  /**
   * @return \DateTime
   */
  public function getFechaYHora(): ?\DateTimeInterface {
    return $this->fechaYHora;
  }

  public function setFechaYHora(\DateTimeInterface $fechaYHora): static {
    $this->fechaYHora = $fechaYHora;

    return $this;
  }
}
