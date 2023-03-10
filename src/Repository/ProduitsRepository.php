<?php

namespace App\Repository;

use App\Entity\Produits;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produits>
 *
 * @method Produits|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produits|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produits[]    findAll()
 * @method Produits[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produits::class);
    }

    public function save(Produits $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Produits $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Produits[] Returns an array of Produits objects
     */
    public function findByReference(string $reference): ?array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.reference = :reference')
            ->setParameter('reference', $reference)
            ->setMaxResults(3)
            ->getQuery()
            ->getResult()
        ;
    }
    public function findAllCategoriesByGender(): ?array
    {
        return $this->createQueryBuilder('p')
            ->select('distinct p.categorie', 'p.public')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Produits[] Returns an array of Produits objects
     */
    public function findAllgroupByReference(
        int $page = 1,
        string $sortBy = 'id',
        int $perPage = 36
    ): ?array
    {
        $query = $this->createQueryBuilder('p')
            ->select('p')
            ->groupBy('p.reference')
            ->setMaxResults(36);

        switch ($sortBy) {
            case 'desc_price':
                $query->orderBy('p.prix', 'desc');
                break;
            case 'asc_price':
                $query->orderBy('p.prix', 'asc');
                break;
            case 'desc_rate':
                $query->orderBy('p.note', 'desc');
                break;
            case 'asc_rate':
                $query->orderBy('p.note', 'asc');
                break;
            default:
                $query->orderBy('p.id');
                break;
        }
        $query->setMaxResults($perPage)
            ->setFirstResult(($page * $perPage) - $perPage);


        $paginator = new Paginator($query);
        $data = $paginator->getQuery()->getResult();

        if (empty($data)) {
            return [];
        }

        $pages = ceil($paginator->count() / $perPage);


        return [
            'results' => $data,
            'limit' => $perPage,
            'pages' => $pages,
            'page' => $page,
            'numberResults' => $paginator->count(),
        ];
    }

    public function findAllReferenceByCategoryAndPublic(
        string $categorie, string $public, int $page = 1,
        string $sortBy = 'id',
        int $perPage = 36
    ): ?array
    {
        $query = $this->createQueryBuilder('p')
            ->select('p')
            ->groupBy('p.reference')
            ->andWhere('p.categorie = :categorie')
            ->andWhere('p.public = :public')
            ->setParameter('categorie', $categorie)
            ->setParameter('public', $public);


        switch ($sortBy) {
            case 'desc_price':
                $query->orderBy('p.prix', 'desc');
                break;
            case 'asc_price':
                $query->orderBy('p.prix', 'asc');
                break;
            case 'desc_rate':
                $query->orderBy('p.note', 'desc');
                break;
            case 'asc_rate':
                $query->orderBy('p.note', 'asc');
                break;
            default:
                $query->orderBy('p.id');
                break;
        }
        $query->setMaxResults($perPage)
            ->setFirstResult(($page * $perPage) - $perPage);


        $paginator = new Paginator($query);
        $data = $paginator->getQuery()->getResult();

        if (empty($data)) {
            return [];
        }

        $pages = ceil($paginator->count() / $perPage);


        return [
            'results' => $data,
            'limit' => $perPage,
            'pages' => $pages,
            'page' => $page,
            'numberResults' => $paginator->count(),
        ];
    }

    public function findAllReferenceByCategory(
        string $categorie, int $page = 1,
        string $sortBy = 'id',
        int $perPage = 36
    ): ?array
    {
        $query = $this->createQueryBuilder('p')
            ->select('p')
            ->groupBy('p.reference')
            ->andWhere('p.categorie = :categorie')
            ->setParameter('categorie', $categorie)
            ->setMaxResults(36);

        switch ($sortBy) {
            case 'desc_price':
                $query->orderBy('p.prix', 'desc');
                break;
            case 'asc_price':
                $query->orderBy('p.prix', 'asc');
                break;
            case 'desc_rate':
                $query->orderBy('p.note', 'desc');
                break;
            case 'asc_rate':
                $query->orderBy('p.note', 'asc');
                break;
            default:
                $query->orderBy('p.id');
                break;
        }
        $query->setMaxResults($perPage)
            ->setFirstResult(($page * $perPage) - $perPage);


        $paginator = new Paginator($query);
        $data = $paginator->getQuery()->getResult();

        if (empty($data)) {
            return [];
        }

        $pages = ceil($paginator->count() / $perPage);


        return [
            'results' => $data,
            'limit' => $perPage,
            'pages' => $pages,
            'page' => $page,
            'numberResults' => $paginator->count(),
        ];
    }
    public function findAllReferenceBySearchTerm(
        string $term,
        int $page = 1,
        string $sortBy = 'id',
        int $perPage = 36
    ): ?array
    {
        $query = $this->createQueryBuilder('p')
            ->select('p')
            ->groupBy('p.reference')
            ->where('p.titre LIKE :term')
            // ->orWhere('p.description LIKE :term')
            ->orWhere('p.categorie LIKE :term')
            ->orWhere('p.reference LIKE :term')
            ->setParameter('term', '%' . $term . '%');

        switch ($sortBy) {
            case 'desc_price':
                $query->orderBy('p.prix', 'desc');
                break;
            case 'asc_price':
                $query->orderBy('p.prix', 'asc');
                break;
            case 'desc_rate':
                $query->orderBy('p.note', 'desc');
                break;
            case 'asc_rate':
                $query->orderBy('p.note', 'asc');
                break;
            default:
                $query->orderBy('p.id');
                break;
        }
        $query->setMaxResults($perPage)
            ->setFirstResult(($page * $perPage) - $perPage);


        $paginator = new Paginator($query);
        $data = $paginator->getQuery()->getResult();

        if (empty($data)) {
            return [];
        }

        $pages = ceil($paginator->count() / $perPage);


        return [
            'results' => $data,
            'limit' => $perPage,
            'pages' => $pages,
            'page' => $page,
            'numberResults' => $paginator->count(),
        ];
    }
}