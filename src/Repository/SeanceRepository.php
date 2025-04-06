<?php

namespace App\Repository;

use App\Entity\Seance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Seance>
 */
class SeanceRepository extends ServiceEntityRepository
{
  private Security $security;

  public function __construct(ManagerRegistry $registry, Security $security)
  {
    parent::__construct($registry, Seance::class);
    $this->security = $security;
  }

  /**
   * @return Seance[] Returns an array of Seance objects
   */
  public function getAllByActiveUser(): array
  {
    $user = $this->security->getUser();

    return $this->createQueryBuilder('s')
      ->andWhere('s.user = :user')
      ->setParameter('user', $user)
      ->orderBy('s.id', 'DESC')
      ->getQuery()
      ->getResult()
    ;
  }

  //    public function findOneBySomeField($value): ?Seance
  //    {
  //        return $this->createQueryBuilder('s')
  //            ->andWhere('s.exampleField = :val')
  //            ->setParameter('val', $value)
  //            ->getQuery()
  //            ->getOneOrNullResult()
  //        ;
  //    }
}
