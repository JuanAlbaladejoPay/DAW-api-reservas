<?php

namespace App\Repository;

use App\Entity\Reserva;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reserva>
 *
 * @method Reserva|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reserva|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reserva[]    findAll()
 * @method Reserva[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservaRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Reserva::class);
  }

  /**
   * @return Reserva[] Returns an array of Reserva objects
   */
  // Devolvería lo mismo que: SELECT * FROM reservas WHERE fecha = "2024-04-24" and hora > "12:00" and hora < "14:30";
  public function findReservasByDayAndHour($day, $startHour, $endHour): array {
    return $this->createQueryBuilder('r')
      ->andWhere('r.fecha = :day')
      ->andWhere('r.hora > :startHour')
      ->andWhere('r.hora < :endHour')
      ->setParameter('day', $day)
      ->setParameter('startHour', $startHour)
      ->setParameter('endHour', $endHour)
      ->orderBy('r.id', 'ASC')
      ->getQuery()
      ->getResult();
  }

  //    public function findOneBySomeField($value): ?Reserva
  //    {
  //        return $this->createQueryBuilder('r')
  //            ->andWhere('r.exampleField = :val')
  //            ->setParameter('val', $value)
  //            ->getQuery()
  //            ->getOneOrNullResult()
  //        ;
  //    }
}
