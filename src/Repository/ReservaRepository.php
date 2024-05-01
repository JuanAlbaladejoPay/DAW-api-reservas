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
  // Devolvería lo mismo que: SELECT * FROM reservas WHERE fechaYHora >= '2024-04-24 12:00:00'  AND fechaYHora <= '2024-04-24 14:30:00';
  public function findReservasByDayAndHour($fechaInicioComprobacion, $fechaFinComprobacion): array {
    return $this->createQueryBuilder('r')
      ->andWhere('r.fechaYHora >= :startDateTime')
      ->andWhere('r.fechaYHora < :endDateTime')
      ->setParameter('startDateTime', $fechaInicioComprobacion)
      ->setParameter('endDateTime', $fechaFinComprobacion)
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
