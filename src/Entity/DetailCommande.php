<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\DetailCommandeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetailCommandeRepository::class)]
#[ApiResource]
class DetailCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'id_detail_commande')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commande $id_commande = null;

    #[ORM\ManyToOne(inversedBy: 'id_detail_commande')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produits $id_produit = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $prix = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdCommande(): ?Commande
    {
        return $this->id_commande;
    }

    public function setIdCommande(?Commande $id_commande): self
    {
        $this->id_commande = $id_commande;

        return $this;
    }

    public function getIdProduit(): ?Produits
    {
        return $this->id_produit;
    }

    public function setIdProduit(?Produits $id_produit): self
    {
        $this->id_produit = $id_produit;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): self
    {
        $this->prix = $prix;

        return $this;
    }
}