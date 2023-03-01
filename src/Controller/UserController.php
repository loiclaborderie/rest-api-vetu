<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\User;
use App\Repository\CommandeRepository;
use App\Repository\DetailCommandeRepository;
use App\Repository\ProduitsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    private $manager;
    private $user;
    private $commandeRepository;
    private $produitsRepository;
    private $detailCommandeRepository;

    public function __construct(EntityManagerInterface $manager, UserRepository $user, CommandeRepository $commandeRepository, ProduitsRepository $produitsRepository, DetailCommandeRepository $detailCommandeRepository)
    {
        $this->manager = $manager;
        $this->user = $user;
        $this->commandeRepository = $commandeRepository;
        $this->produitsRepository = $produitsRepository;
        $this->detailCommandeRepository = $detailCommandeRepository;
    }



    #[Route('/user/create', name: 'user_create', methods: 'POST')]
    public function userCreate(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'];
        $password = $data["password"];
        $pseudo = $data["pseudo"];
        $adresse = $data["adresse"];
        $ville = $data["ville"];
        $nom = $data["nom"];
        $prenom = $data["prenom"];
        $cp = $data["code_postal"];
        $civilite = $data["civilite"];
        $telephone = null;
        if (isset($data["telephone"])) {
            $telephone = $data["telephone"];
        }

        $email_exist = $this->user->findOneByEmail($email);

        if ($email_exist) {
            return new JsonResponse(
                ['status' => false, 'message' => 'Cet email existe déjà']
            );
        } else {
            $user = new User();
            $user->setEmail($email)
                ->setPassword(sha1($password))
                ->setPseudo($pseudo)
                ->setAdresse($adresse)
                ->setVille($ville)
                ->setNom($nom)
                ->setPrenom($prenom)
                ->setCodePostal($cp)
                ->setCivilite($civilite)
                ->setTelephone($telephone);
            $this->manager->persist($user);
            $this->manager->flush();
            return new JsonResponse(
                ['status' => true, 'message' => 'Utilisateur enregistré']
            );
        }
    }

    #[Route('/userUpdate/{id}', name: 'user_update', methods: 'PUT')]
    public function userUpdate($id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'];
        $pseudo = $data["pseudo"];
        $adresse = $data["adresse"];
        $ville = $data["ville"];
        $nom = $data["nom"];
        $prenom = $data["prenom"];
        $cp = $data["code_postal"];
        $civilite = $data["civilite"];
        $telephone = $data["telephone"];

        $user = $this->user->find($id);

        $user->setEmail($email)
            ->setPseudo($pseudo)
            ->setAdresse($adresse)
            ->setVille($ville)
            ->setNom($nom)
            ->setPrenom($prenom)
            ->setCodePostal($cp)
            ->setCivilite($civilite)
            ->setTelephone($telephone);
        $this->manager->persist($user);
        $this->manager->flush();
        return new JsonResponse(
            ['status' => true, 'message' => 'Vos informations ont bien été modifées']
        );
    }


    #[Route('/userUpdate/password/{id}', name: 'user_password_update', methods: 'PUT')]
    public function changePassword($id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $password = $data['password'];
        $newPassword = $data["newPassword"];

        $user = $this->user->find($id);

        $mdp = $user->getPassword();
        $checkPassword = sha1($password);
        $newPassword = sha1($newPassword);

        if ($mdp !== $checkPassword) {
            return new JsonResponse(
                ['status' => false, 'message' => 'Le mot de passe ne correspond pas à celui qui existe']
            );
        }

        $user->setPassword($newPassword);

        $this->manager->persist($user);
        $this->manager->flush();
        return new JsonResponse(
            ['status' => true, 'newmdp' => $newPassword]
        );
    }


    #[Route('/api2/getAllusers', name: 'get_allusers', methods: 'GET')]
    public function getAllUsers(): Response
    {
        $users = $this->user->findAll();
        return $this->json($users, 200);

    }

    #[Route('/user/{id}', name: 'user_by_id', methods: 'GET')]
    public function getUserById($id)
    {
        $user = $this->user->find($id);
        return $this->json($user, 200);
    }

    #[Route('/getAllcommandesByUser/{userId}', name: 'get_allcommandes_by_user', methods: 'GET')]
    public function getAllCommandesByUser(int $userId): Response
    {
        $orders = $this->commandeRepository->findBy(['id_user' => $userId]);
        $ordersWithDetails = [];
        foreach ($orders as $order) {
            $orderWithDetails = [
                'id' => $order->getId(),
                'date' => $order->getDateEnrg(),
                'montant' => $order->getMontant(),
                'statut' => $order->getStatut(),
                'details' => []
            ];

            foreach ($order->getIdDetailCommande() as $detailCommand) {
                $detailCommandWithProduct = [
                    'id' => $detailCommand->getId(),
                    'quantite' => $detailCommand->getQuantite(),
                    'produit' => [
                        'id' => $detailCommand->getIdProduit()->getId(),
                        'titre' => $detailCommand->getIdProduit()->getTitre(),
                        'taille' => $detailCommand->getIdProduit()->getTaille(),
                        'reference' => $detailCommand->getIdProduit()->getReference(),
                        'photo' => $detailCommand->getIdProduit()->getPhoto(),
                        'prix' => $detailCommand->getIdProduit()->getPrix(),
                        'stock' => $detailCommand->getIdProduit()->getStock(),
                    ]
                ];
                $orderWithDetails['details'][] = $detailCommandWithProduct;
            }

            $ordersWithDetails[] = $orderWithDetails;
        }

        return $this->json($ordersWithDetails, 200);
    }


    #[Route('/getAllPastcommandesByUser/{userId}', name: 'get_all_pastcommandes_by_user', methods: 'GET')]
    public function getAllPastCommandesByUser(int $userId): Response
    {
        $orders = $this->commandeRepository->findBy(['id_user' => $userId, 'statut' => 'terminée']);
        $ordersWithDetails = [];
        foreach ($orders as $order) {
            $orderWithDetails = [
                'id' => $order->getId(),
                'date' => $order->getDateEnrg(),
                'montant' => $order->getMontant(),
                'statut' => $order->getStatut(),
                'details' => []
            ];

            foreach ($order->getIdDetailCommande() as $detailCommand) {
                $detailCommandWithProduct = [
                    'id' => $detailCommand->getId(),
                    'quantite' => $detailCommand->getQuantite(),
                    'produit' => [
                        'id' => $detailCommand->getIdProduit()->getId(),
                        'titre' => $detailCommand->getIdProduit()->getTitre(),
                        'taille' => $detailCommand->getIdProduit()->getTaille(),
                        'reference' => $detailCommand->getIdProduit()->getReference(),
                        'photo' => $detailCommand->getIdProduit()->getPhoto(),
                        'prix' => $detailCommand->getIdProduit()->getPrix(),
                        'stock' => $detailCommand->getIdProduit()->getStock(),
                    ]
                ];
                $orderWithDetails['details'][] = $detailCommandWithProduct;
            }

            $ordersWithDetails[] = $orderWithDetails;
        }

        return $this->json($ordersWithDetails, 200);
    }


    #[Route('/getCurrentOrderByUser/{userId}', name: 'get_current_order_by_user', methods: 'GET')]
    public function getCurrentOrderByUser(int $userId): JsonResponse
    {
        $order = $this->commandeRepository->findOneBy(['id_user' => $userId, 'statut' => 'en cours']);

        if (!$order) {
            return new JsonResponse(['message' => 'No order found for this user'], 404);
        }

        $orderWithDetails = [
            'id' => $order->getId(),
            'date' => $order->getDateEnrg(),
            'montant' => $order->getMontant(),
            'statut' => $order->getStatut(),
            'details' => []
        ];

        foreach ($order->getIdDetailCommande() as $detailCommand) {
            $detailCommandWithProduct = [
                'id' => $detailCommand->getId(),
                'quantite' => $detailCommand->getQuantite(),
                'produit' => [
                    'id' => $detailCommand->getIdProduit()->getId(),
                    'titre' => $detailCommand->getIdProduit()->getTitre(),
                    'taille' => $detailCommand->getIdProduit()->getTaille(),
                    'reference' => $detailCommand->getIdProduit()->getReference(),
                    'photo' => $detailCommand->getIdProduit()->getPhoto(),
                    'prix' => $detailCommand->getIdProduit()->getPrix(),
                    'stock' => $detailCommand->getIdProduit()->getStock(),
                ]
            ];
            $orderWithDetails['details'][] = $detailCommandWithProduct;
        }

        return $this->json($orderWithDetails, 200);
    }
}