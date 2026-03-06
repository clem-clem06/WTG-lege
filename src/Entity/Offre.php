<?php

namespace App\Entity;

use App\Repository\OffreRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OffreRepository::class)]
class Offre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?int $nombreUnites = null;

    #[ORM\Column]
    private ?int $prixMensuel = null;

    #[ORM\Column(nullable: true)]
    private ?int $prixAnnuel = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getNombreUnites(): ?int
    {
        return $this->nombreUnites;
    }

    public function setNombreUnites(int $nombreUnites): static
    {
        $this->nombreUnites = $nombreUnites;

        return $this;
    }

    public function getPrixMensuel(): ?int
    {
        return $this->prixMensuel;
    }

    public function setPrixMensuel(int $prixMensuel): static
    {
        $this->prixMensuel = $prixMensuel;

        return $this;
    }

    public function getPrixAnnuel(): ?int
    {
        return $this->prixAnnuel;
    }

    public function setPrixAnnuel(?int $prixAnnuel): static
    {
        $this->prixAnnuel = $prixAnnuel;

        return $this;
    }
}
