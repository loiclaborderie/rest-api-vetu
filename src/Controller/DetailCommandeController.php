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

    #[Route('/detailcommande/add/{id}', name: 'app_detail_commande_add_to_commande', methods: 'POST')]
    public function addItemToCommande($id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $id_produit = $data['id_produit'];
        $quantite = $data['quantite'];

        $commande = $this->commandeRep->find($id);
        $produit = $this->produitRep->find($id_produit);

        $detailCommande = new DetailCommande();
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

    #[Route('/detailcommande/get/{id}', name: 'app_detail_commande_for_commande', methods: 'GET')]
    function getAllItemsFromCommande($id): Response
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
                'stock' => $detailCommand->getIdProduit()->getStock(),
            ];
        }, $detailCommandes);
        return new JsonResponse($orderWithDetails, 200);
    }
}