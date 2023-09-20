<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TrafegoGoogle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TrafegoGoogle>
 *
 * @method TrafegoGoogle|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrafegoGoogle|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrafegoGoogle[]    findAll()
 * @method TrafegoGoogle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrafegoGoogleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrafegoGoogle::class);
    }

    public function save(TrafegoGoogle $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TrafegoGoogle $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
