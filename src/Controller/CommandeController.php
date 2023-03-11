<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use App\Repository\DetailCommandeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{

    private $manager;
    private $commande;
    private $userRepository;
    private $detailCommande;

    public function __construct(EntityManagerInterface $manager, CommandeRepository $commande, UserRepository $userRepository, DetailCommandeRepository $detailCommande)
    {
        $this->manager = $manager;
        $this->commande = $commande;
        $this->userRepository = $userRepository;
        $this->detailCommande = $detailCommande;
    }


    #[Route('/commande/create/{id}', name: 'app_commande_create', methods: 'POST')]
    public function createCommande($id): JsonResponse
    {
        $user = $this->userRepository->find($id);


        $commande = new Commande();
        $commande->setMontant(0);
        $commande->setDateEnrg(new \DateTime('now'));
        $commande->setStatut('en cours');
        $commande->setIdUser($user);

        $this->manager->persist($commande);
        $this->manager->flush();

        return new JsonResponse(
            ['created' => $commande->getId()]
        );
    }


    #[Route('/commande/finish/{id}', name: 'app_commande_finish', methods: 'GET')]
    public function finishCommande($id): JsonResponse
    {
        // $user = $this->userRepository->find($id);
        $commande = $this->commande->find($id);
        $total = 0;
        foreach ($commande->getIdDetailCommande() as $detailCommande) {
            $total += $detailCommande->getPrix();
        }

        $commande->setMontant($total);
        $commande->setStatut('attente_livraison');

        $this->manager->persist($commande);
        $this->manager->flush();

        return new JsonResponse(
            ['commande passée', 200]
        );
    }


    #[Route('/commande/delete/{id}', name: 'app_commande_delete', methods: 'DELETE')]
    public function deleteCommande($id): JsonResponse
    {
        $commande = $this->commande->find($id);

        if (!$commande) {
            return new JsonResponse(
                ['error' => 'Commande not found']
            );
        }

        if ($commande->getStatut() === 'en cours') {
            foreach ($commande->getIdDetailCommande() as $detailCommande) {
                $quantite = $detailCommande->getQuantite();
                $produit = $detailCommande->getIdProduit();
                $stock = $produit->getStock();
                $produit->setStock($stock + $quantite);
                $this->manager->remove($detailCommande);
            }

            $this->manager->flush();

            return new JsonResponse(
                ["La commande $id est bien vide", 200]
            );
        } else {
            return new JsonResponse(
                ['error' => 'No ongoing order']
            );
        }

    }
    #[Route('/commande/delete/{id}/product', name: 'app_commande_product_delete', methods: 'PUT')]
    public function deleteProductFromCommande($id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $produitId = $data['id'];

        $commande = $this->commande->find($id);
        $detailCommande = $this->detailCommande->findOneBy(['id_produit' => $produitId]);

        if (!$commande) {
            return new JsonResponse(
                ['error' => 'Commande not found']
            );
        }
        if (!$detailCommande) {
            return new JsonResponse(
                ['error' => 'Product not found']
            );
        }

        $quantite = $detailCommande->getQuantite();
        $produit = $detailCommande->getIdProduit();
        $stock = $produit->getStock();
        $produit->setStock($stock + $quantite);
        $commande->removeIdDetailCommande($detailCommande);
        $this->manager->remove($detailCommande);
        $this->manager->flush();

        return new JsonResponse(
            ["Le produit $produitId a bien été supprimé de votre commande", 200]
        );

    }
}