<?php

namespace App\Controller;

use App\Repository\ProduitsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProduitsController extends AbstractController
{

    public function __construct(private ProduitsRepository $produitsRepository)
    {
        $this->produitsRepository = $produitsRepository;
    }


    #[Route("/produits/categories", name: "produits_distinct_categories", methods: "GET")]

    public function getAllCategories(): JsonResponse
    {
        $categories = $this->produitsRepository->findAllCategoriesByGender();

        return $this->json($categories);
    }

    #[Route("/produits/distinct-reference", name: "produits_distinct_reference", methods: "GET")]

    public function getAllDistinctReference(): JsonResponse
    {
        $references = $this->produitsRepository->findAllDistinctReference();

        return $this->json($references);
    }

    #[Route("/produits/categorie/{categorie}", name: "produits_reference_by_categorie", methods: "GET")]

    public function getAllReferenceByCategory(string $categorie): JsonResponse
    {
        $references = $this->produitsRepository->findAllReferenceByCategory($categorie);

        return $this->json($references);
    }

    #[Route("/produits/categorie/{categorie}/{public}", name: "produits_reference_by_categorie_public", methods: "GET")]

    public function getAllReferenceByCategoryandPublic(string $categorie, string $public): JsonResponse
    {
        $references = $this->produitsRepository->findAllReferenceByCategoryAndPublic($categorie, $public);

        return $this->json($references);
    }

    #[Route("/produits/search/{term}", name: "produits_reference_searchterm", methods: "GET")]

    public function getAllProductsBySearchTerm(
        string $term,
        Request $request
    ): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $sortBy = $request->query->get('sortBy', 'id');
        $references = $this->produitsRepository->findAllReferenceBySearchTerm($term, $page, $sortBy);

        return $this->json($references);
    }

    #[Route("/produits/reference/{reference}", name: "produits_by_reference", methods: "GET")]
    public function getByReference(int $reference): JsonResponse
    {
        $produits = $this->produitsRepository->findByReference($reference);

        return $this->json($produits);
    }

}