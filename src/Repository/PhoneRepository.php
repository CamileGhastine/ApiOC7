<?php

namespace App\Repository;

use App\Entity\Phone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Phone|null find($id, $lockMode = null, $lockVersion = null)
 * @method Phone|null findOneBy(array $criteria, array $orderBy = null)
 * @method Phone[]    findAll()
 * @method Phone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PhoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Phone::class);
    }

    /**
     * @param array $parameters
     *
     * @return Paginator
     */
    public function findPhonePaginated(array $parameters)
    {
        extract($parameters);

        $query = $this->createQueryBuilder('p')
            ->setFirstResult(($page-1)*$maxResult)
            ->setMaxResults($maxResult)
            ->Where('p.price >= :minVal')
            ->setParameter('minVal', $price[0])
            ->andWhere('p.price <= :maxVal')
            ->setParameter('maxVal', $price[1])
            ->orderBy('p.price', 'ASC')
        ;

        if ($brand) {
            $query->andwhere('p.brand = :val')
                ->setParameter('val', $brand)
            ;
        }

        $query->getQuery();

        return new Paginator($query);
    }

    /**
     * @param string|null $brand
     *
     * @return int|mixed|string
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countAll(?string $brand, array $price)
    {
        $query = $this->createQueryBuilder('p')
            ->Where('p.price >= :minVal')
            ->setParameter('minVal', $price[0])
            ->andWhere('p.price <= :maxVal')
            ->setParameter('maxVal', $price[1]);

        if ($brand) {
            $query->andwhere('p.brand = :val')
                ->setParameter('val', $brand)
            ;
        }

        return $query
            ->select('COUNT(p)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Phone
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
