<?php

namespace App\Repository;

use App\Entity\Metric;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Metric>
 *
 * @method Metric|null find($id, $lockMode = null, $lockVersion = null)
 * @method Metric|null findOneBy(array $criteria, array $orderBy = null)
 * @method Metric[]    findAll()
 * @method Metric[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MetricRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Metric::class);
    }

    public function add(Metric $entity): void
    {
        $this->getEntityManager()->persist($entity);
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function remove(Metric $entity): void
    {
        $this->getEntityManager()->remove($entity);
    }
}
