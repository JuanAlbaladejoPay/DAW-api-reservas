<?php

namespace App\Repository;

use App\Entity\Instalacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Instalacion>
 *
 * @method Instalacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Instalacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Instalacion[]    findAll()
 * @method Instalacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InstalacionRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Instalacion::class);
  }

  //    /**
  //     * @return Instalacion[] Returns an array of Instalacion objects
  //     */
  //    public function findByExampleField($value): array
  //    {
  //        return $this->createQueryBuilder('i')
  //            ->andWhere('i.exampleField = :val')
  //            ->setParameter('val', $value)
  //            ->orderBy('i.id', 'ASC')
  //            ->setMaxResults(10)
  //            ->getQuery()
  //            ->getResult()
  //        ;
  //    }

  //    public function findOneBySomeField($value): ?Instalacion
  //    {
  //        return $this->createQueryBuilder('i')
  //            ->andWhere('i.exampleField = :val')
  //            ->setParameter('val', $value)
  //            ->getQuery()
  //            ->getOneOrNullResult()
  //        ;
  //    }
}
