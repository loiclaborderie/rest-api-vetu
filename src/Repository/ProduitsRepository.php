<?php

namespace App\Repository;

use App\Entity\Produits;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
    public function findAllgroupByReference(): ?array
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->groupBy('p.reference')
            ->setMaxResults(36)
            ->getQuery()
            ->getResult();
    }

    public function findAllReferenceByCategoryAndPublic(string $categorie, string $public): ?array
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->groupBy('p.reference')
            ->andWhere('p.categorie = :categorie')
            ->andWhere('p.public = :public')
            ->setParameter('categorie', $categorie)
            ->setParameter('public', $public)
            ->setMaxResults(36)
            ->getQuery()
            ->getResult();
    }

    public function findAllReferenceByCategory(string $categorie): ?array
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->groupBy('p.reference')
            ->andWhere('p.categorie = :categorie')
            ->setParameter('categorie', $categorie)
            ->setMaxResults(36)
            ->getQuery()
            ->getResult();
    }
    public function findAllReferenceBySearchTerm(string $term): ?array
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->groupBy('p.reference')
            ->where('p.titre LIKE :term')
            ->orWhere('p.description LIKE :term')
            ->orWhere('p.categorie LIKE :term')
            ->orWhere('p.reference LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->setMaxResults(36)
            ->getQuery()
            ->getResult();
    }
}