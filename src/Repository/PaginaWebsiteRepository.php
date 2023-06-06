<?php

declare(strict_types=1);

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
}
