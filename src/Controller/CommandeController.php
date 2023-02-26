<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{

    private $manager;
    private $commande;

    public function __construct(EntityManagerInterface $manager, CommandeRepository $commande)
    {
        $this->manager = $manager;
        $this->commande = $commande;
    }


    #[Route('/commande/userId/{id}', name: 'app_commande_by_user_id', methods: 'GET')]
    public function getByUserId($id): Response
    {
        $commandes = $this->commande->findAllByUserId($id);
        return $this->json($commandes, 200);
    }
}