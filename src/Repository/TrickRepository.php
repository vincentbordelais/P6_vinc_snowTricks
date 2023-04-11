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

    /**
     * @return Trick[] Returns an array of Trick objects paginated
     */
    public function findTricksPaginated(int $currentPage, int $limit = 10): array
    {
        $result = [];

        $tricks = $this->getEntityManager()->createQueryBuilder()
            ->select('t')
            ->from('App\Entity\Trick', 't')
            ->orderBy('t.updatedDate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult(($currentPage * $limit) - $limit);

        $paginator = new Paginator($tricks);
        $tricksPerPage = $paginator->getQuery()->getResult();

        // On vérifie qu'on a des données :
        if (empty($tricksPerPage)) {
            return $result;
        }

        // On calcule le nombre de pages :
        $totalOfPages = ceil($paginator->count() / $limit); // ceil = arrondi supérieur, $paginator->count() = nbre de tricks

        // On remplie notre tableau $result :
        $result['tricksPerPage']  = $tricksPerPage;
        $result['totalOfPages'] = $totalOfPages;
        $result['limit'] = $limit;

        return $result;
    }

    /**
     * @return Trick[] Returns an array of Trick objects by category paginated
     */
    public function findTricksByCategoryPaginated($categorySlug, int $currentPage, int $limit = 10): array
    {
        $result = [];

        $tricks = $this->getEntityManager()->createQueryBuilder()
            ->select('t, c')
            ->from('App\Entity\Trick', 't')
            ->join('t.categories', 'c')
            ->where('c.slug = :categorySlug')
            ->setParameter('categorySlug', $categorySlug)
            ->orderBy('t.updatedDate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult(($currentPage * $limit) - $limit);

        // dump($tricks);
        $paginator = new Paginator($tricks);
        $tricksPerPage = $paginator->getQuery()->getResult();

        // On vérifie qu'on a des données :
        if (empty($tricksPerPage || $categorySlug == null)) {
            return $result;
        }

        // On calcule le nombre de pages :
        $totalOfPages = ceil($paginator->count() / $limit); // ceil = arrondi supérieur, $paginator->count() = nbre de tricks

        // On remplie notre tableau $result :
        $result['tricksPerPage']  = $tricksPerPage;
        $result['totalOfPages'] = $totalOfPages;
        $result['limit'] = $limit;

        return $result;
    }
}
