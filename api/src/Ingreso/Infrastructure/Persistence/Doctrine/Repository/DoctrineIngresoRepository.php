<?php

declare(strict_types=1);

namespace App\Ingreso\Infrastructure\Persistence\Doctrine\Repository;

use App\Ingreso\Domain\Model\Ingreso;
use App\Ingreso\Domain\Model\IngresoCategoria;
use App\Ingreso\Domain\Model\IngresoId;
use App\Ingreso\Domain\Repository\IngresoRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
            ->setParameter('desde', $desde)
            ->setParameter('hasta', $hasta)
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
            ->setParameter('desde', $desde)
            ->setParameter('hasta', $hasta)
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
}
