<?php

declare(strict_types=1);

namespace App\Gasto\Infrastructure\Persistence\Doctrine\Repository;

use App\Gasto\Domain\Model\Gasto;
use App\Gasto\Domain\Model\GastoCategoria;
use App\Gasto\Domain\Model\GastoId;
use App\Gasto\Domain\Repository\GastoRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Gasto>
 */
final class DoctrineGastoRepository extends ServiceEntityRepository implements GastoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gasto::class);
    }

    public function save(Gasto $gasto): void
    {
        $this->getEntityManager()->persist($gasto);
        $this->getEntityManager()->flush();
    }

    public function remove(Gasto $gasto): void
    {
        $this->getEntityManager()->remove($gasto);
        $this->getEntityManager()->flush();
    }

    public function findById(GastoId $id): ?Gasto
    {
        return $this->find($id->value);
    }

    /**
     * @return Gasto[]
     */
    public function findAll(): array
    {
        return parent::findAll();
    }

    /**
     * @return Gasto[]
     */
    public function findActiveGastos(): array
    {
        return $this->createQueryBuilder('g')
            ->where('g.deletedAt IS NULL')
            ->orderBy('g.fecha', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Gasto[]
     */
    public function findByLocalId(int $localId): array
    {
        return $this->createQueryBuilder('g')
            ->join('g.local', 'l')
            ->where('l.id = :localId')
            ->andWhere('g.deletedAt IS NULL')
            ->setParameter('localId', $localId)
            ->orderBy('g.fecha', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Gasto[]
     */
    public function findByCategoria(GastoCategoria $categoria): array
    {
        return $this->createQueryBuilder('g')
            ->where('g.categoria = :categoria')
            ->andWhere('g.deletedAt IS NULL')
            ->setParameter('categoria', $categoria)
            ->orderBy('g.fecha', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Gasto[]
     */
    public function findByDateRange(\DateTimeImmutable $desde, \DateTimeImmutable $hasta): array
    {
        return $this->createQueryBuilder('g')
            ->where('g.fecha BETWEEN :desde AND :hasta')
            ->andWhere('g.deletedAt IS NULL')
            ->setParameter('desde', $desde)
            ->setParameter('hasta', $hasta)
            ->orderBy('g.fecha', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Gasto[]
     */
    public function findByLocalAndDateRange(
        int $localId,
        \DateTimeImmutable $desde,
        \DateTimeImmutable $hasta
    ): array {
        return $this->createQueryBuilder('g')
            ->join('g.local', 'l')
            ->where('l.id = :localId')
            ->andWhere('g.fecha BETWEEN :desde AND :hasta')
            ->andWhere('g.deletedAt IS NULL')
            ->setParameter('localId', $localId)
            ->setParameter('desde', $desde)
            ->setParameter('hasta', $hasta)
            ->orderBy('g.fecha', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Gasto[]
     */
    public function findByLocalAndCategoria(int $localId, GastoCategoria $categoria): array
    {
        return $this->createQueryBuilder('g')
            ->join('g.local', 'l')
            ->where('l.id = :localId')
            ->andWhere('g.categoria = :categoria')
            ->andWhere('g.deletedAt IS NULL')
            ->setParameter('localId', $localId)
            ->setParameter('categoria', $categoria)
            ->orderBy('g.fecha', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getTotalImporteByLocal(int $localId): float
    {
        $result = $this->createQueryBuilder('g')
            ->select('SUM(g.importe) as total')
            ->join('g.local', 'l')
            ->where('l.id = :localId')
            ->andWhere('g.deletedAt IS NULL')
            ->setParameter('localId', $localId)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (float) $result : 0.0;
    }

    public function getTotalImporteByLocalAndCategoria(int $localId, GastoCategoria $categoria): float
    {
        $result = $this->createQueryBuilder('g')
            ->select('SUM(g.importe) as total')
            ->join('g.local', 'l')
            ->where('l.id = :localId')
            ->andWhere('g.categoria = :categoria')
            ->andWhere('g.deletedAt IS NULL')
            ->setParameter('localId', $localId)
            ->setParameter('categoria', $categoria)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (float) $result : 0.0;
    }

    public function getTotalImporteByLocalAndDateRange(
        int $localId,
        \DateTimeImmutable $desde,
        \DateTimeImmutable $hasta
    ): float {
        $result = $this->createQueryBuilder('g')
            ->select('SUM(g.importe) as total')
            ->join('g.local', 'l')
            ->where('l.id = :localId')
            ->andWhere('g.fecha BETWEEN :desde AND :hasta')
            ->andWhere('g.deletedAt IS NULL')
            ->setParameter('localId', $localId)
            ->setParameter('desde', $desde)
            ->setParameter('hasta', $hasta)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (float) $result : 0.0;
    }

    public function count(array $criteria = []): int
    {
        return parent::count($criteria);
    }

    public function getTotalImporteByDateRange(\DateTimeImmutable $desde, \DateTimeImmutable $hasta): float
    {
        $result = $this->createQueryBuilder('g')
            ->select('SUM(g.importe) as total')
            ->where('g.fecha BETWEEN :desde AND :hasta')
            ->andWhere('g.deletedAt IS NULL')
            ->setParameter('desde', $desde)
            ->setParameter('hasta', $hasta)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (float) $result : 0.0;
    }
}
