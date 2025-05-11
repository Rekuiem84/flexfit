<?php

namespace App\Repository;

use App\Entity\OrderHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderHistory>
 */
class OrderHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderHistory::class);
    }

    public function getMostPopularProducts(int $limit = 3): array
    {
        $qb = $this->createQueryBuilder('oh')
            ->select('p.id, p.name, p.price, m.url, SUM(oi.quantity) as total_quantity')
            ->join('oh.orderItems', 'oi')
            ->join('oi.product', 'p')
            ->leftJoin('p.media', 'm', 'WITH', 'm.isMainImage = true')
            ->groupBy('p.id, p.name, p.price, m.url')
            ->orderBy('total_quantity', 'DESC')
            ->setMaxResults($limit);


        $results = $qb->getQuery()->getResult();

        // Transform the results to match the expected format
        return array_map(function ($item) {
            return [
                'id' => $item['id'],
                'name' => $item['name'],
                'price' => $item['price'],
                'mainImage' => $item['url'] ? ['url' => $item['url']] : null,
                'total_quantity' => $item['total_quantity']
            ];
        }, $results);
    }

    public function getMostSoldItemsByMonth(int $limit = 3): array
    {
        $qb = $this->createQueryBuilder('oh')
            ->select('p.id, p.name, p.price, m.url, SUM(oi.quantity) as total_quantity, 
                    DATE_FORMAT(oh.createdAt, \'%Y-%m\') as month')
            ->join('oh.orderItems', 'oi')
            ->join('oi.product', 'p')
            ->leftJoin('p.media', 'm', 'WITH', 'm.isMainImage = true')
            ->where('oh.createdAt >= :lastMonth')
            ->setParameter('lastMonth', new \DateTime('-1 month'))
            ->groupBy('p.id, p.name, p.price, m.url, month')
            ->orderBy('month', 'DESC')
            ->addOrderBy('total_quantity', 'DESC')
            ->setMaxResults($limit * 2); // Get top items for both months

        $results = $qb->getQuery()->getResult();

        // Organize results by month
        $organizedResults = [];
        foreach ($results as $item) {
            $month = $item['month'];
            if (!isset($organizedResults[$month])) {
                $organizedResults[$month] = [];
            }

            if (count($organizedResults[$month]) < $limit) {
                $organizedResults[$month][] = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'mainImage' => $item['url'] ? ['url' => $item['url']] : null,
                    'total_quantity' => $item['total_quantity']
                ];
            }
        }

        return $organizedResults;
    }

    //    /**
    //     * @return OrderHistory[] Returns an array of OrderHistory objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?OrderHistory
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
