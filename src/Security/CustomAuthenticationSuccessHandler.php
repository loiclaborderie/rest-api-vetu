<?php
// src/Security/CustomAuthenticationSuccessHandler.php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;


class CustomAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private $jwtManager;

    public function __construct(JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        // Get the user ID from the token
        $userId = $token->getUser()->getId();

        // Generate JWT token
        $jwt = $this->jwtManager->create($token->getUser());

        // Create the response
        $response = new Response(json_encode(['userId' => $userId, 'token' => $jwt]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}