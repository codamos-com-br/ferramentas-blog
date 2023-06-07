<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ArquivoWeb;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArquivoWeb>
 *
 * @method ArquivoWeb|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArquivoWeb|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArquivoWeb[]    findAll()
 * @method ArquivoWeb[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArquivoWebRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArquivoWeb::class);
    }

    public function save(ArquivoWeb $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ArquivoWeb $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /** @return iterable<ArquivoWeb> */
    public function findHeavyFiles(int $thresholdBytes): iterable
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.tamanhoBytes > :tamanho')
            ->setParameter('tamanho', $thresholdBytes)
            ->orderBy('a.tamanhoBytes', 'DESC')
            ->orderBy('a.fonte', 'ASC')
            ->getQuery()
            ->toIterable()
        ;
    }
}
