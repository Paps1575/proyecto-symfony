<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] // <-- Así, sin paréntesis ni clases extra
class ImagenCarrusel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $ruta = null;

    public function getId(): ?int { return $this->id; }
    public function getRuta(): ?string { return $this->ruta; }
    public function setRuta(string $ruta): self { $this->ruta = $ruta; return $this; }
}
