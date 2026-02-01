<?php

declare(strict_types=1);

namespace App\Trastero\Infrastructure\Persistence\Doctrine\Repository;

use App\Trastero\Domain\Model\Trastero;
use App\Trastero\Domain\Model\TrasteroEstado;
use App\Trastero\Domain\Model\TrasteroId;
use App\Trastero\Domain\Repository\TrasteroRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trastero>
 */
final class DoctrineTrasteroRepository extends ServiceEntityRepository implements TrasteroRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trastero::class);
    }

    public function save(Trastero $trastero): void
    {
        $this->getEntityManager()->persist($trastero);
        $this->getEntityManager()->flush();
    }

    public function remove(Trastero $trastero): void
    {
        $this->getEntityManager()->remove($trastero);
        $this->getEntityManager()->flush();
    }

    public function findById(TrasteroId $id): ?Trastero
    {
        return $this->find($id->value);
    }

    public function findByNumeroAndLocal(string $numero, int $localId): ?Trastero
    {
        return $this->createQueryBuilder('t')
            ->join('t.local', 'l')
            ->where('t.numero = :numero')
            ->andWhere('l.id = :localId')
            ->andWhere('t.deletedAt IS NULL')
            ->setParameter('numero', $numero)
            ->setParameter('localId', $localId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Trastero[]
     */
    public function findAll(): array
    {
        return parent::findAll();
    }

    /**
     * @return Trastero[]
     */
    public function findActiveTrasteros(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.deletedAt IS NULL')
            ->orderBy('t.numero', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Trastero[]
     */
    public function findByLocalId(int $localId): array
    {
        return $this->createQueryBuilder('t')
            ->join('t.local', 'l')
            ->where('l.id = :localId')
            ->andWhere('t.deletedAt IS NULL')
            ->setParameter('localId', $localId)
            ->orderBy('t.numero', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Trastero[]
     */
    public function findByEstado(TrasteroEstado $estado): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.estado = :estado')
            ->andWhere('t.deletedAt IS NULL')
            ->setParameter('estado', $estado)
            ->orderBy('t.numero', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Trastero[]
     */
    public function findByLocalAndEstado(int $localId, TrasteroEstado $estado): array
    {
        return $this->createQueryBuilder('t')
            ->join('t.local', 'l')
            ->where('l.id = :localId')
            ->andWhere('t.estado = :estado')
            ->andWhere('t.deletedAt IS NULL')
            ->setParameter('localId', $localId)
            ->setParameter('estado', $estado)
            ->orderBy('t.numero', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Trastero[]
     */
    public function findDisponiblesByLocal(int $localId): array
    {
        return $this->findByLocalAndEstado($localId, TrasteroEstado::DISPONIBLE);
    }

    /**
     * @return Trastero[]
     */
    public function findOcupadosByLocal(int $localId): array
    {
        return $this->findByLocalAndEstado($localId, TrasteroEstado::OCUPADO);
    }

    public function countByLocal(int $localId): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->join('t.local', 'l')
            ->where('l.id = :localId')
            ->andWhere('t.deletedAt IS NULL')
            ->setParameter('localId', $localId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countByLocalAndEstado(int $localId, TrasteroEstado $estado): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->join('t.local', 'l')
            ->where('l.id = :localId')
            ->andWhere('t.estado = :estado')
            ->andWhere('t.deletedAt IS NULL')
            ->setParameter('localId', $localId)
            ->setParameter('estado', $estado)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTotalSuperficieByLocal(int $localId): float
    {
        $result = $this->createQueryBuilder('t')
            ->select('SUM(t.superficie) as total')
            ->join('t.local', 'l')
            ->where('l.id = :localId')
            ->andWhere('t.deletedAt IS NULL')
            ->setParameter('localId', $localId)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (float) $result : 0.0;
    }

    public function getTotalIngresosMensualesByLocal(int $localId): float
    {
        $result = $this->createQueryBuilder('t')
            ->select('SUM(t.precioMensual) as total')
            ->join('t.local', 'l')
            ->where('l.id = :localId')
            ->andWhere('t.deletedAt IS NULL')
            ->setParameter('localId', $localId)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (float) $result : 0.0;
    }

    public function getTotalIngresosMensualesOcupadosByLocal(int $localId): float
    {
        $result = $this->createQueryBuilder('t')
            ->select('SUM(t.precioMensual) as total')
            ->join('t.local', 'l')
            ->where('l.id = :localId')
            ->andWhere('t.estado = :estado')
            ->andWhere('t.deletedAt IS NULL')
            ->setParameter('localId', $localId)
            ->setParameter('estado', TrasteroEstado::OCUPADO)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (float) $result : 0.0;
    }

    public function existsByNumeroAndLocal(string $numero, int $localId): bool
    {
        return $this->findByNumeroAndLocal($numero, $localId) !== null;
    }
}
