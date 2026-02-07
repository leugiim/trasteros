<?php

declare(strict_types=1);

namespace App\Ingreso\Infrastructure\Persistence\Doctrine\Repository;

use App\Ingreso\Domain\Model\Ingreso;
use App\Ingreso\Domain\Model\IngresoCategoria;
use App\Ingreso\Domain\Model\IngresoId;
use App\Ingreso\Domain\Repository\IngresoRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ingreso>
 */
final class DoctrineIngresoRepository extends ServiceEntityRepository implements IngresoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ingreso::class);
    }

    public function save(Ingreso $ingreso): void
    {
        $this->getEntityManager()->persist($ingreso);
        $this->getEntityManager()->flush();
    }

    public function remove(Ingreso $ingreso): void
    {
        $this->getEntityManager()->remove($ingreso);
        $this->getEntityManager()->flush();
    }

    public function findById(IngresoId $id): ?Ingreso
    {
        return $this->find($id->value);
    }

    /**
     * @return Ingreso[]
     */
    public function findAll(): array
    {
        return parent::findAll();
    }

    /**
     * @return Ingreso[]
     */
    public function findActiveIngresos(): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.deletedAt IS NULL')
            ->orderBy('i.fechaPago', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Ingreso[]
     */
    public function findByContratoId(int $contratoId): array
    {
        return $this->createQueryBuilder('i')
            ->join('i.contrato', 'c')
            ->where('c.id = :contratoId')
            ->andWhere('i.deletedAt IS NULL')
            ->setParameter('contratoId', $contratoId)
            ->orderBy('i.fechaPago', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Ingreso[]
     */
    public function findByCategoria(IngresoCategoria $categoria): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.categoria = :categoria')
            ->andWhere('i.deletedAt IS NULL')
            ->setParameter('categoria', $categoria)
            ->orderBy('i.fechaPago', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Ingreso[]
     */
    public function findByDateRange(\DateTimeImmutable $desde, \DateTimeImmutable $hasta): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.fechaPago BETWEEN :desde AND :hasta')
            ->andWhere('i.deletedAt IS NULL')
            ->setParameter('desde', $desde, Types::DATE_IMMUTABLE)
            ->setParameter('hasta', $hasta, Types::DATE_IMMUTABLE)
            ->orderBy('i.fechaPago', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Ingreso[]
     */
    public function findByContratoAndDateRange(
        int $contratoId,
        \DateTimeImmutable $desde,
        \DateTimeImmutable $hasta
    ): array {
        return $this->createQueryBuilder('i')
            ->join('i.contrato', 'c')
            ->where('c.id = :contratoId')
            ->andWhere('i.fechaPago BETWEEN :desde AND :hasta')
            ->andWhere('i.deletedAt IS NULL')
            ->setParameter('contratoId', $contratoId)
            ->setParameter('desde', $desde, Types::DATE_IMMUTABLE)
            ->setParameter('hasta', $hasta, Types::DATE_IMMUTABLE)
            ->orderBy('i.fechaPago', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Ingreso[]
     */
    public function findByContratoAndCategoria(int $contratoId, IngresoCategoria $categoria): array
    {
        return $this->createQueryBuilder('i')
            ->join('i.contrato', 'c')
            ->where('c.id = :contratoId')
            ->andWhere('i.categoria = :categoria')
            ->andWhere('i.deletedAt IS NULL')
            ->setParameter('contratoId', $contratoId)
            ->setParameter('categoria', $categoria)
            ->orderBy('i.fechaPago', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getTotalImporteByContrato(int $contratoId): float
    {
        $result = $this->createQueryBuilder('i')
            ->select('SUM(i.importe) as total')
            ->join('i.contrato', 'c')
            ->where('c.id = :contratoId')
            ->andWhere('i.deletedAt IS NULL')
            ->setParameter('contratoId', $contratoId)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (float) $result : 0.0;
    }

    public function getTotalImporteByContratoAndCategoria(int $contratoId, IngresoCategoria $categoria): float
    {
        $result = $this->createQueryBuilder('i')
            ->select('SUM(i.importe) as total')
            ->join('i.contrato', 'c')
            ->where('c.id = :contratoId')
            ->andWhere('i.categoria = :categoria')
            ->andWhere('i.deletedAt IS NULL')
            ->setParameter('contratoId', $contratoId)
            ->setParameter('categoria', $categoria)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (float) $result : 0.0;
    }

    public function getTotalImporteByContratoAndDateRange(
        int $contratoId,
        \DateTimeImmutable $desde,
        \DateTimeImmutable $hasta
    ): float {
        $result = $this->createQueryBuilder('i')
            ->select('SUM(i.importe) as total')
            ->join('i.contrato', 'c')
            ->where('c.id = :contratoId')
            ->andWhere('i.fechaPago BETWEEN :desde AND :hasta')
            ->andWhere('i.deletedAt IS NULL')
            ->setParameter('contratoId', $contratoId)
            ->setParameter('desde', $desde, Types::DATE_IMMUTABLE)
            ->setParameter('hasta', $hasta, Types::DATE_IMMUTABLE)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (float) $result : 0.0;
    }

    public function count(array $criteria = []): int
    {
        return parent::count($criteria);
    }

    /**
     * @return Ingreso[]
     */
    public function findByTrasteroId(int $trasteroId): array
    {
        return $this->createQueryBuilder('i')
            ->join('i.contrato', 'c')
            ->join('c.trastero', 't')
            ->where('t.id = :trasteroId')
            ->andWhere('i.deletedAt IS NULL')
            ->setParameter('trasteroId', $trasteroId)
            ->orderBy('i.fechaPago', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Ingreso[]
     */
    public function findByLocalId(int $localId): array
    {
        return $this->createQueryBuilder('i')
            ->join('i.contrato', 'c')
            ->join('c.trastero', 't')
            ->join('t.local', 'l')
            ->where('l.id = :localId')
            ->andWhere('i.deletedAt IS NULL')
            ->setParameter('localId', $localId)
            ->orderBy('i.fechaPago', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getTotalImporteByTrastero(int $trasteroId): float
    {
        $result = $this->createQueryBuilder('i')
            ->select('SUM(i.importe) as total')
            ->join('i.contrato', 'c')
            ->join('c.trastero', 't')
            ->where('t.id = :trasteroId')
            ->andWhere('i.deletedAt IS NULL')
            ->setParameter('trasteroId', $trasteroId)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (float) $result : 0.0;
    }

    public function getTotalImporteByLocal(int $localId): float
    {
        $result = $this->createQueryBuilder('i')
            ->select('SUM(i.importe) as total')
            ->join('i.contrato', 'c')
            ->join('c.trastero', 't')
            ->join('t.local', 'l')
            ->where('l.id = :localId')
            ->andWhere('i.deletedAt IS NULL')
            ->setParameter('localId', $localId)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (float) $result : 0.0;
    }

    public function getTotalImporteByDateRange(\DateTimeImmutable $desde, \DateTimeImmutable $hasta): float
    {
        $result = $this->createQueryBuilder('i')
            ->select('SUM(i.importe) as total')
            ->where('i.fechaPago BETWEEN :desde AND :hasta')
            ->andWhere('i.deletedAt IS NULL')
            ->setParameter('desde', $desde, Types::DATE_IMMUTABLE)
            ->setParameter('hasta', $hasta, Types::DATE_IMMUTABLE)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (float) $result : 0.0;
    }

    /**
     * @return array<array{date: string, total: float}>
     */
    public function getImportesGroupedByDay(\DateTimeImmutable $desde, \DateTimeImmutable $hasta): array
    {
        $results = $this->createQueryBuilder('i')
            ->select('SUBSTRING(i.fechaPago, 1, 10) as date, SUM(i.importe) as total')
            ->where('i.fechaPago BETWEEN :desde AND :hasta')
            ->andWhere('i.deletedAt IS NULL')
            ->setParameter('desde', $desde, Types::DATE_IMMUTABLE)
            ->setParameter('hasta', $hasta, Types::DATE_IMMUTABLE)
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(function ($row) {
            return [
                'date' => $row['date'],
                'total' => (float) $row['total']
            ];
        }, $results);
    }

    /**
     * @return array<array{date: string, total: float}>
     */
    public function getImportesGroupedByMonth(\DateTimeImmutable $desde, \DateTimeImmutable $hasta): array
    {
        $results = $this->createQueryBuilder('i')
            ->select('SUBSTRING(i.fechaPago, 1, 7) as date, SUM(i.importe) as total')
            ->where('i.fechaPago BETWEEN :desde AND :hasta')
            ->andWhere('i.deletedAt IS NULL')
            ->setParameter('desde', $desde, Types::DATE_IMMUTABLE)
            ->setParameter('hasta', $hasta, Types::DATE_IMMUTABLE)
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(function ($row) {
            return [
                'date' => $row['date'],
                'total' => (float) $row['total']
            ];
        }, $results);
    }
}
