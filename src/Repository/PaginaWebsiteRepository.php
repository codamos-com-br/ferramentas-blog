<?php

namespace App\Repository;

use App\Entity\PaginaWebsite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaginaWebsite>
 *
 * @method PaginaWebsite|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaginaWebsite|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaginaWebsite[]    findAll()
 * @method PaginaWebsite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaginaWebsiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaginaWebsite::class);
    }

    public function save(PaginaWebsite $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PaginaWebsite $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PaginaWebsite[] Returns an array of PaginaWebsite objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PaginaWebsite
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
