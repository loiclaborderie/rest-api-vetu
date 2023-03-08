<?php

namespace App\Controller;

use App\Entity\DetailCommande;
use App\Repository\CommandeRepository;
use App\Repository\DetailCommandeRepository;
use App\Repository\ProduitsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DetailCommandeController extends AbstractController
{

    private $manager;
    private $detailCommandeRep;
    private $commandeRep;
    private $produitRep;

    public function __construct(EntityManagerInterface $manager, CommandeRepository $commandeRepository, DetailCommandeRepository $detailCommandeRepository, ProduitsRepository $produitsRepository)
    {
        $this->manager = $manager;
        $this->commandeRep = $commandeRepository;
        $this->produitRep = $produitsRepository;
        $this->detailCommandeRep = $detailCommandeRepository;
    }


    #[Route('/detailcommande/change/{id}', name: 'app_detail_commande_update_commande_quantity', methods: 'PATCH')]
    public function changeDetailCommandeQuantity($id, Request $request): JsonResponse
    {
        $commande = $this->commandeRep->find($id);
        $data = json_decode($request->getContent(), true);
        $quantity = $data['quantite'];
        $idproduit = $data['id'];
        $detailCommande = $this->detailCommandeRep->findBy(['id_commande' => $commande, 'id_produit' => $idproduit]);
        $produit = $this->produitRep->find($idproduit);
        $stock = $produit->getStock();
        $prix = $produit->getPrix();

        if (!empty($detailCommande)) {
            $previousQuantity = $detailCommande[0]->getQuantite();
            $diff = $quantity - $previousQuantity;

            if ($stock >= $diff) {
                $produit->setStock($stock - $diff);
                $detailCommande[0]->setQuantite($quantity);
                $detailCommande[0]->setPrix($quantity * $prix);
                $this->manager->persist($produit);
                $this->manager->persist($detailCommande[0]);
                $this->manager->flush();
                return new JsonResponse(["new quantity = $quantity", 200]);
            } else {
                return new JsonResponse(["Quantité insuffisante", 400]);
            }
        } else {
            return new JsonResponse(['failed', 400]);
        }
    }



    #[Route('/detailcommande/add/{id}', name: 'app_detail_commande_add_to_commande', methods: 'POST')]
    public function addItemToCommande($id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $id_produit = $data['id_produit'];
        $quantite = $data['quantite'];

        $commande = $this->commandeRep->find($id);
        $produit = $this->produitRep->find($id_produit);

        if ($produit->getStock() < $quantite) {
            return new JsonResponse(
                ['stock insuffisant', 400]
            );
        }

        $detailCommande = $this->manager->getRepository(DetailCommande::class)
            ->findOneBy(['id_commande' => $commande, 'id_produit' => $produit]);

        if ($detailCommande) {
            $finalQuantity = $detailCommande->getQuantite() + $quantite;
            $detailCommande->setQuantite($finalQuantity);
            $stock = $produit->getStock();
            $produit->setStock($stock - $quantite);
            $detailCommande->setPrix($produit->getPrix() * $detailCommande->getQuantite());
            $this->manager->persist($detailCommande);
            $this->manager->flush();
            return new JsonResponse(
                ['cela a bien fonctionné', 200]
            );
        } else {
            $detailCommande = new DetailCommande();
            $stock = $produit->getStock();
            $stock = $produit->getStock();
            $produit->setStock($stock - $quantite);
            $detailCommande->setIdCommande($commande);
            $detailCommande->setIdProduit($produit);
            $detailCommande->setQuantite($quantite);
            $detailCommande->setPrix($produit->getPrix() * $quantite);

            $this->manager->persist($detailCommande);
            $this->manager->flush();

            return new JsonResponse(
                ['cela a bien fonctionné', 200]
            );
        }
    }

    #[Route('/detailcommande/get/{id}', name: 'app_detail_commande_for_commande', methods: 'GET')]
    function getAllItemsFromCommande($id): JsonResponse
    {
        $detailCommandes = $this->detailCommandeRep->findBy(['id_commande' => $id]);
        $orderWithDetails = array_map(function ($detailCommand) {
            return (object) [
                'id' => $detailCommand->getIdProduit()->getId(),
                'quantite' => $detailCommand->getQuantite(),
                'titre' => $detailCommand->getIdProduit()->getTitre(),
                'taille' => $detailCommand->getIdProduit()->getTaille(),
                'reference' => $detailCommand->getIdProduit()->getReference(),
                'photo' => $detailCommand->getIdProduit()->getPhoto(),
                'prix' => $detailCommand->getIdProduit()->getPrix(),
                'stock' => $detailCommand->getIdProduit()->getStock() + $detailCommand->getQuantite(),
            ];
        }, $detailCommandes);
        return new JsonResponse($orderWithDetails, 200);
    }
}