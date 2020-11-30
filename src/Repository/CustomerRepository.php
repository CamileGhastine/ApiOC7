<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Customer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Customer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Customer[]    findAll()
 * @method Customer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    /**
     * @param array $parameters
     *
     * @return Paginator
     */
    public function findCustomersPaginated(array $parameters, int $userId)
    {
        extract($parameters);

        $query = $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            -> where('u.id = :userId')
            ->setParameter('userId', $userId)
            ->setFirstResult(($page-1)*$maxResult)
            ->setMaxResults($maxResult)
            ->orderBy('p.id', 'ASC')
            ->getQuery();

        return new Paginator($query);
    }

    /**
     * @param int $id
     * @param int $userId
     *
     * @return int|mixed|string
     */
    public function findCustomerByUser(int $id, int $userId)
    {
        return $query = $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')
            ->where('c.id = :id')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $userId)
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();

    }

    /**
     * @return int|mixed|string
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countAll($userId)
    {
        return $query = $this->createQueryBuilder('c')
            ->select('COUNT(c)')
            ->leftJoin('c.user', 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    // /**
    //  * @return Customer[] Returns an array of Customer objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Customer
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
