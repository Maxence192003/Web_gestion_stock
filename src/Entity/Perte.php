<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\PerteRepository;
use App\State\PerteStateProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PerteRepository::class)]
#[ApiResource(operations: [
    new Get(),
    new GetCollection(),
    new Post(processor: PerteStateProcessor::class),
])]
class Perte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\Positive]
    private ?int $quantite = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $raison = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $datePerte = null;

    #[ORM\ManyToOne(inversedBy: 'pertes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Produit $produit = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getRaison(): ?string
    {
        return $this->raison;
    }

    public function setRaison(string $raison): static
    {
        $this->raison = $raison;

        return $this;
    }

    public function getDatePerte(): ?\DateTimeImmutable
    {
        return $this->datePerte;
    }

    public function setDatePerte(\DateTimeImmutable $datePerte): static
    {
        $this->datePerte = $datePerte;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;

        return $this;
    }
}