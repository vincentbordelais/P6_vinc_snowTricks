<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function save(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Comment[] Returns an array of Comment objects paginated
     */
    public function findCommentsPaginated($trickSlug, int $currentPage, int $limit = 10): array
    {
        $result = [];
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('c, u, t') // récupère les objets Comment, User et Trick
            ->from('App\Entity\Comment', 'c')
            ->join('c.user', 'u')
            ->join('c.trick', 't')
            ->where('t.slug = :trickSlug')
            ->setParameter('trickSlug', $trickSlug)
            ->setMaxResults($limit) // retourne seulement les résultats $limit
            ->setFirstResult(($currentPage * $limit) - $limit) // saute les premiers résultats
            ->orderBy('c.created_date', 'ASC');

        // normalement on ferait: $result = $query->getQuery()->getResult();
        $paginator = new Paginator($query);
        $commentsData = $paginator->getQuery()->getResult();

        // On vérifie qu'on a des données :
        if (empty($commentsData)) {
            return $result; // tableau vide
        }
        // On calcule le nombre de pages :
        $totalNumberOfPages = ceil($paginator->count() / $limit); // ceil = arrondi supérieur

        // On remplie notre tableau $result :
        $result['commentsData'] = $commentsData;
        $result['currentPage'] = $currentPage;
        $result['totalNumberOfPages'] = $totalNumberOfPages;
        $result['limit'] = $limit;
        return $result;
    }
}
