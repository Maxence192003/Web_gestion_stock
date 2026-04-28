<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\ProduitStockInput;
use App\Repository\ProduitRepository;
use App\State\ProduitStockUpdateProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ApiResource(operations: [
    new Get(),
    new GetCollection(),
    new Post(),
    new Patch(),
    new Delete(),
    new Patch(
        uriTemplate: '/mobile/produits/{id}/stock',
        input: ProduitStockInput::class,
        processor: ProduitStockUpdateProcessor::class,
        name: 'mobile_product_stock_update',
    ),
])]
class Produit
{
    public const TYPE_BOISSON = 'boisson';
    public const TYPE_NOURRITURE = 'nourriture';
    public const TYPE_AUTRE = 'autre';

    public const AVAILABLE_TYPES = [
        self::TYPE_BOISSON,
        self::TYPE_NOURRITURE,
        self::TYPE_AUTRE,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $nom = null;

    #[ORM\Column(length: 50, options: ['default' => self::TYPE_AUTRE])]
    #[Assert\Choice(choices: self::AVAILABLE_TYPES)]
    private ?string $typeProduit = self::TYPE_AUTRE;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\PositiveOrZero]
    private ?string $prixAchat = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\PositiveOrZero]
    private ?string $prixVente = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private ?int $quantiteStock = null;

    /** @var Collection<int, Vente> */
    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: Vente::class)]
    private Collection $ventes;

    /** @var Collection<int, Perte> */
    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: Perte::class)]
    private Collection $pertes;

    public function __construct()
    {
        $this->ventes = new ArrayCollection();
        $this->pertes = new ArrayCollection();
    }

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

    public function getTypeProduit(): ?string
    {
        return $this->typeProduit;
    }

    public function setTypeProduit(string $typeProduit): static
    {
        $this->typeProduit = $typeProduit;

        return $this;
    }

    public function getPrixAchat(): ?string
    {
        return $this->prixAchat;
    }

    public function setPrixAchat(string $prixAchat): static
    {
        $this->prixAchat = $prixAchat;

        return $this;
    }

    public function getPrixVente(): ?string
    {
        return $this->prixVente;
    }

    public function setPrixVente(string $prixVente): static
    {
        $this->prixVente = $prixVente;

        return $this;
    }

    public function getQuantiteStock(): ?int
    {
        return $this->quantiteStock;
    }

    public function setQuantiteStock(int $quantiteStock): static
    {
        $this->quantiteStock = $quantiteStock;

        return $this;
    }

    /** @return Collection<int, Vente> */
    public function getVentes(): Collection
    {
        return $this->ventes;
    }

    public function addVente(Vente $vente): static
    {
        if (!$this->ventes->contains($vente)) {
            $this->ventes->add($vente);
            $vente->setProduit($this);
        }

        return $this;
    }

    public function removeVente(Vente $vente): static
    {
        if ($this->ventes->removeElement($vente) && $vente->getProduit() === $this) {
            $vente->setProduit(null);
        }

        return $this;
    }

    /** @return Collection<int, Perte> */
    public function getPertes(): Collection
    {
        return $this->pertes;
    }

    public function addPerte(Perte $perte): static
    {
        if (!$this->pertes->contains($perte)) {
            $this->pertes->add($perte);
            $perte->setProduit($this);
        }

        return $this;
    }

    public function removePerte(Perte $perte): static
    {
        if ($this->pertes->removeElement($perte) && $perte->getProduit() === $this) {
            $perte->setProduit(null);
        }

        return $this;
    }
}