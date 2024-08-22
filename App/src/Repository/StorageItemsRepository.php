<?php

namespace App\Repository;

use App\Entity\StorageItems;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StorageItems>
 */
class StorageItemsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StorageItems::class);
    }

    /**
     * @return StorageItems Returns a single storageItem object using barcode and the StorageId
     */
    public function findStorageItemByBarcodeAndStorageId($barcode, $storageId)
    {
        return $this->createQueryBuilder('st')
            ->innerJoin('st.productId', 'p')
            ->where('st.storageId = :storageId')
            ->andWhere('p.barcode = :barcode')
            ->setParameter('barcode', $barcode)
            ->setParameter('storageId', $storageId)
            ->getQuery()->getOneOrNullResult();
    }

    public function findStorageItemByProductIdAndStorageId($productId, $storageId)
    {
        return $this->createQueryBuilder('st')
            ->innerJoin('st.productId', 'p')
            ->where('st.storageId = :storageId')
            ->andWhere('p.id = :productId')
            ->setParameter('productId', $productId)
            ->setParameter('storageId', $storageId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return StorageItems[] Returns an array of StorageItems objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?StorageItems
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
