<?php

namespace App\Controller;

use App\Repository\ProduitsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProduitsController extends AbstractController
{

    public function __construct(private ProduitsRepository $produitsRepository)
    {
        $this->produitsRepository = $produitsRepository;
    }


    #[Route("/produits/distinct-reference", name: "produits_distinct_reference", methods: "GET")]

    public function getAllDistinctReference(): JsonResponse
    {
        $references = $this->produitsRepository->findAllDistinctReference();

        return $this->json($references);
    }

    #[Route("/produits/reference/{reference}", name: "produits_by_reference", methods: "GET")]
    public function getByReference(int $reference): JsonResponse
    {
        $produits = $this->produitsRepository->findByReference($reference);

        return $this->json($produits);
    }

}