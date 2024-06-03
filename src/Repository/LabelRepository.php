<?php

namespace App\Repository;

use App\Entity\Album;
use App\Entity\Label;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Label>
 *
 * @method Label|null find($id, $lockMode = null, $lockVersion = null)
 * @method Label|null findOneBy(array $criteria, array $orderBy = null)
 * @method Label[]    findAll()
 * @method Label[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LabelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Label::class);
    }

//    /**
//     * @return Label[] Returns an array of Label objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Label
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
public function findAlbumAndLabelNames(): array
    {
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();

        $qb->select('a', 'label.name as labelName')
            ->from(Album::class, 'a')
            ->leftJoin('a.artist_user_id_user_id', 'lAl')
            ->leftJoin('lAl.label', 'label')
            ->where($qb->expr()->orX(
                $qb->expr()->between('a.create_at', 'lAl.added_at', 'COALESCE(lAl.quitted_at, CURRENT_TIMESTAMP())'),
                $qb->expr()->isNull('label.name')
            ));

        return $qb->getQuery()->getResult();
    }
}
