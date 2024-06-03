<?php

namespace App\Repository;

use App\Entity\Artist;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Album;
use App\Entity\ArtistHasLabel;
use App\Entity\Label;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @extends ServiceEntityRepository<Artist>
 *
 * @method Artist|null find($id, $lockMode = null, $lockVersion = null)
 * @method Artist|null findOneBy(array $criteria, array $orderBy = null)
 * @method Artist[]    findAll()
 * @method Artist[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArtistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Artist::class);
    }

    //    /**
    //     * @return Artist[] Returns an array of Artist objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    public function findOneBySomeField($userId)
    {
        return $this->createQueryBuilder('a')
            ->where('a.User_idUser = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function GetExiteFullname($fullname)
    {
        return $this->createQueryBuilder('a')
            ->select('count(a.User_idUser)')
            ->where('a.fullname = :fullname')
            ->setParameter('fullname', $fullname)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function findAllWithPagination($page, $limit)
    {
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
    
        $qb->select('ar','l.name')
            ->from(Artist::class, 'ar')
            ->leftJoin(Album::class,'a',join::WITH,'a.artist_User_idUser =ar.id')
            ->leftJoin(ArtistHasLabel::class,'ahl',join::WITH,'ahl.idArtist =ar.id')
            ->leftJoin(Label::class,'l',join::WITH,'l.id = ahl.id_label')
            ->where( 
                $qb->expr()->orX(
                    $qb->expr()->between(
                        'a.createAt',
                        'ahl.addedAt',
                        $qb->expr()->literal('COALESCE(ahl.quittedAt, CURRENT_TIMESTAMP())')
                    ),
                    $qb->expr()->isNull('l.name'),
                    $qb->expr()->isNotNull('l.name')
                )
                );
    
            
        // Pagination
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
    
        return $qb->getQuery()->getResult();
    }
    
    public function countArtist()
    {
        $qb = $this->createQueryBuilder('a')
            ->select('count(a.id)');
        return $qb->getQuery()       // Convert the QueryBuilder instance into a Query object
            ->getSingleScalarResult();
    }
    public function findAlbumAndLabelNames(Artist $a)
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
            )
            );

        return $qb->getQuery()->getResult();
    }


}
