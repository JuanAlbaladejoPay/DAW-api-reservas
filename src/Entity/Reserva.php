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

  #[ORM\Column(type: Types::DATE_MUTABLE)]
  private ?\DateTimeInterface $fecha = null;

  #[ORM\Column(type: Types::TIME_MUTABLE)]
  private ?\DateTimeInterface $hora = null;

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


  public function getId(): ?int {
    return $this->id;
  }

  public function getFecha(): ?\DateTimeInterface {
    return $this->fecha;
  }

  public function setFecha(\DateTimeInterface $fecha): static {
    $this->fecha = $fecha;

    return $this;
  }

  public function getHora(): ?\DateTimeInterface {
    return $this->hora;
  }

  public function setHora(\DateTimeInterface $hora): static {
    $this->hora = $hora;

    return $this;
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
}
