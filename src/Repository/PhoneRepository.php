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
     * @param $page
     * @param $maxResult
     * @return Paginator
     */
    public function findPhonePaginated(int $page, ?int $maxResult, ?string $sortBy)
    {
        $query = $this->createQueryBuilder('p')
            ->setFirstResult(($page-1)*$maxResult)
            ->setMaxResults($maxResult)
            ;

        if ($sortBy) {
            $query->where('p.brand = :val')
                ->setParameter('val', $sortBy)
                ;
        }

        $query->getQuery();

        return new Paginator($query);
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
