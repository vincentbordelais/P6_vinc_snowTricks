<?php

namespace App\Repository;

use App\Entity\Trick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trick>
 *
 * @method Trick|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trick|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trick[]    findAll()
 * @method Trick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrickRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trick::class);
    }

    public function save(Trick $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Trick $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return Trick[] Returns an array of Trick objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Trick
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * @return Trick[] Returns an array of Trick objects paginated
     */
    public function findTricksPaginated(int $NumberOfThePage, int $limit = 10): array
    {
        $result = [];

        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('t')
            ->from('App\Entity\Trick', 't')
            ->setMaxResults($limit)
            ->setFirstResult(($NumberOfThePage * $limit) - $limit);
        // $result = $query->getQuery()->getResult();

        $paginator = new Paginator($query);
        $data = $paginator->getQuery()->getResult();
        // dd($data);

        // On vérifie qu'on a des données :
        if (empty($data)) {
            return $result;
        }

        // On calcule le nombre de pages :
        $totalOfPages = ceil($paginator->count() / $limit); // ceil = arrondi supérieur, $paginator->count() = nbre de tricks

        // On rempli notre tableau $result :
        $result['data']  = $data;
        $result['NumberOfThePage'] = $NumberOfThePage;
        $result['totalOfPages'] = $totalOfPages;
        $result['limit'] = $limit;

        return $result;
    }
}
