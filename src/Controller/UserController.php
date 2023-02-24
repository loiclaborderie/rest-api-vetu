<?php

namespace App\Controller;

use App\Entity\User;
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

    public function __construct(EntityManagerInterface $manager, UserRepository $user)
    {
        $this->manager = $manager;
        $this->user = $user;
    }



    #[Route('/userCreate', name: 'user_create', methods: 'POST')]
    public function userCreate(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'];
        $password = $data["password"];

        $email_exist = $this->user->findOneByEmail($email);

        if ($email_exist) {
            return new JsonResponse(
                ['status' => false, 'message' => 'Cet email existe déjà']
            );
        } else {
            $user = new User();
            $user->setEmail($email)
                ->setPassword(sha1($password));
            $this->manager->persist($user);
            $this->manager->flush();
            return new JsonResponse(
                ['status' => true, 'message' => 'Utilisateur enregistré']
            );
        }
    }


    #[Route('/getAllusers', name: 'get_allusers', methods: 'GET')]
    public function getAllUsers(): Response
    {
        $users = $this->user->findAll();
        return $this->json($users, 200);

    }
}