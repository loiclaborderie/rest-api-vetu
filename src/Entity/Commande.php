<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
#[ApiResource]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'id_commande')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $id_user = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $montant = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_enrg = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = null;

    #[ORM\OneToMany(mappedBy: 'id_commande', targetEntity: DetailCommande::class)]
    private Collection $id_detail_commande;

    public function __construct()
    {
        $this->id_detail_commande = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdUser(): ?User
    {
        return $this->id_user;
    }

    public function setIdUser(?User $id_user): self
    {
        $this->id_user = $id_user;

        return $this;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(?string $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getDateEnrg(): ?\DateTimeInterface
    {
        return $this->date_enrg;
    }

    public function setDateEnrg(?\DateTimeInterface $date_enrg): self
    {
        $this->date_enrg = $date_enrg;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * @return Collection<int, DetailCommande>
     */
    public function getIdDetailCommande(): Collection
    {
        return $this->id_detail_commande;
    }

    public function addIdDetailCommande(DetailCommande $idDetailCommande): self
    {
        if (!$this->id_detail_commande->contains($idDetailCommande)) {
            $this->id_detail_commande->add($idDetailCommande);
            $idDetailCommande->setIdCommande($this);
        }

        return $this;
    }

    public function removeIdDetailCommande(DetailCommande $idDetailCommande): self
    {
        if ($this->id_detail_commande->removeElement($idDetailCommande)) {
            // set the owning side to null (unless already changed)
            if ($idDetailCommande->getIdCommande() === $this) {
                $idDetailCommande->setIdCommande(null);
            }
        }

        return $this;
    }
}