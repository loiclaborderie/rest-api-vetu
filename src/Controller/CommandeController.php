<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
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

    public function __construct(EntityManagerInterface $manager, CommandeRepository $commande, UserRepository $userRepository)
    {
        $this->manager = $manager;
        $this->commande = $commande;
        $this->userRepository = $userRepository;
    }


    #[Route('/commande/create/{id}', name: 'app_commande_create', methods: 'POST')]
    public function createCommande($id): Response
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


    #[Route('/commande/delete/{id}', name: 'app_commande_delete', methods: 'DELETE')]
    public function deleteCommande($id): Response
    {
        $commande = $this->commande->find($id);

        if (!$commande) {
            return new JsonResponse(
                ['error' => 'Commande not found']
            );
        }

        // iterate through the collection of DetailCommande and remove each one
        foreach ($commande->getIdDetailCommande() as $detailCommande) {
            $this->manager->remove($detailCommande);
        }

        $this->manager->flush();

        return new JsonResponse(
            ["La commande $id est bien vide", 200]
        );
    }

}