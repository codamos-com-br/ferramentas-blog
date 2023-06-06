<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\UrlWebsite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UrlWebsite>
 *
 * @method UrlWebsite|null find($id, $lockMode = null, $lockVersion = null)
 * @method UrlWebsite|null findOneBy(array $criteria, array $orderBy = null)
 * @method UrlWebsite[]    findAll()
 * @method UrlWebsite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UrlWebsiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UrlWebsite::class);
    }

    public function save(UrlWebsite $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UrlWebsite $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /** @return iterable<UrlWebsite> */
    public function findUrlsOlderThan(\DateTimeInterface $date): iterable
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.ultimaVisita IS NULL OR u.ultimaVisita <= :data')
            ->setParameter('data', $date)
            ->orderBy('u.prioridade', 'DESC')
            ->getQuery()
            ->toIterable()
        ;
    }
}
