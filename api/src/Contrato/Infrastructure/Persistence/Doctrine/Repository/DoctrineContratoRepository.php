<?php

declare(strict_types=1);

namespace App\Contrato\Infrastructure\Persistence\Doctrine\Repository;

use App\Contrato\Domain\Model\Contrato;
use App\Contrato\Domain\Model\ContratoEstado;
use App\Contrato\Domain\Repository\ContratoRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DoctrineContratoRepository extends ServiceEntityRepository implements ContratoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contrato::class);
    }

    public function save(Contrato $contrato): void
    {
        $this->getEntityManager()->persist($contrato);
        $this->getEntityManager()->flush();
    }

    public function findById(int $id): ?Contrato
    {
        return $this->createQueryBuilder('c')
            ->where('c.id = :id')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.deletedAt IS NULL')
            ->orderBy('c.fechaInicio', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByTrasteroId(int $trasteroId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.trastero = :trasteroId')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('trasteroId', $trasteroId)
            ->orderBy('c.fechaInicio', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByClienteId(int $clienteId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.cliente = :clienteId')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('clienteId', $clienteId)
            ->orderBy('c.fechaInicio', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findContratosActivosByCliente(int $clienteId): array
    {
        $hoy = new \DateTimeImmutable('today');

        return $this->createQueryBuilder('c')
            ->where('c.cliente = :clienteId')
            ->andWhere('c.estado != :cancelado')
            ->andWhere('c.fechaInicio <= :hoy')
            ->andWhere('c.fechaFin IS NULL OR c.fechaFin >= :hoy')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('clienteId', $clienteId)
            ->setParameter('cancelado', ContratoEstado::CANCELADO)
            ->setParameter('hoy', $hoy, 'date_immutable')
            ->orderBy('c.fechaInicio', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findContratosActivosByTrastero(int $trasteroId): array
    {
        $hoy = new \DateTimeImmutable('today');

        return $this->createQueryBuilder('c')
            ->where('c.trastero = :trasteroId')
            ->andWhere('c.estado != :cancelado')
            ->andWhere('c.fechaInicio <= :hoy')
            ->andWhere('c.fechaFin IS NULL OR c.fechaFin >= :hoy')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('trasteroId', $trasteroId)
            ->setParameter('cancelado', ContratoEstado::CANCELADO)
            ->setParameter('hoy', $hoy, 'date_immutable')
            ->orderBy('c.fechaInicio', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findContratosSolapados(
        int $trasteroId,
        \DateTimeImmutable $inicio,
        ?\DateTimeImmutable $fin = null,
        ?int $excludeContratoId = null
    ): array {
        $qb = $this->createQueryBuilder('c')
            ->where('c.trastero = :trasteroId')
            ->andWhere('c.estado != :cancelado')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('trasteroId', $trasteroId)
            ->setParameter('cancelado', ContratoEstado::CANCELADO);

        // Overlap: existing.inicio <= new.fin AND (existing.fin IS NULL OR existing.fin >= new.inicio)
        if ($fin !== null) {
            $qb->andWhere('c.fechaInicio <= :fin')
                ->setParameter('fin', $fin, 'date_immutable');
        }

        $qb->andWhere('c.fechaFin IS NULL OR c.fechaFin >= :inicio')
            ->setParameter('inicio', $inicio, 'date_immutable');

        if ($excludeContratoId !== null) {
            $qb->andWhere('c.id != :excludeId')
                ->setParameter('excludeId', $excludeContratoId);
        }

        return $qb->orderBy('c.fechaInicio', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Contrato[]
     */
    public function findProximosAVencer(int $dias = 30): array
    {
        $hoy = new \DateTimeImmutable('today');
        $limite = $hoy->modify("+{$dias} days");

        return $this->createQueryBuilder('c')
            ->where('c.estado != :cancelado')
            ->andWhere('c.fechaInicio <= :hoy')
            ->andWhere('c.fechaFin IS NOT NULL')
            ->andWhere('c.fechaFin >= :hoy')
            ->andWhere('c.fechaFin <= :limite')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('cancelado', ContratoEstado::CANCELADO)
            ->setParameter('hoy', $hoy, 'date_immutable')
            ->setParameter('limite', $limite, 'date_immutable')
            ->orderBy('c.fechaFin', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Contrato[]
     */
    public function findConFianzaPendiente(): array
    {
        $hoy = new \DateTimeImmutable('today');

        return $this->createQueryBuilder('c')
            ->where('c.estado != :cancelado')
            ->andWhere('c.fechaInicio <= :hoy')
            ->andWhere('c.fechaFin IS NULL OR c.fechaFin >= :hoy')
            ->andWhere('c.fianzaPagada = false')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('cancelado', ContratoEstado::CANCELADO)
            ->setParameter('hoy', $hoy, 'date_immutable')
            ->orderBy('c.fechaInicio', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function remove(Contrato $contrato): void
    {
        $this->getEntityManager()->remove($contrato);
        $this->getEntityManager()->flush();
    }

    public function countContratosActivos(): int
    {
        $hoy = new \DateTimeImmutable('today');

        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.estado != :cancelado')
            ->andWhere('c.fechaInicio <= :hoy')
            ->andWhere('c.fechaFin IS NULL OR c.fechaFin >= :hoy')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('cancelado', ContratoEstado::CANCELADO)
            ->setParameter('hoy', $hoy, 'date_immutable')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countTrasterosOcupados(): int
    {
        $hoy = new \DateTimeImmutable('today');

        return (int) $this->getEntityManager()->createQuery(
            'SELECT COUNT(DISTINCT IDENTITY(c.trastero))
             FROM App\Contrato\Domain\Model\Contrato c
             JOIN c.trastero t
             WHERE c.estado != :cancelado
             AND c.fechaInicio <= :hoy
             AND (c.fechaFin IS NULL OR c.fechaFin >= :hoy)
             AND c.deletedAt IS NULL
             AND t.deletedAt IS NULL
             AND t.estado != :mantenimiento'
        )
            ->setParameter('cancelado', ContratoEstado::CANCELADO)
            ->setParameter('hoy', $hoy, 'date_immutable')
            ->setParameter('mantenimiento', 'mantenimiento')
            ->getSingleScalarResult();
    }

    public function countTrasterosOcupadosByLocal(int $localId): int
    {
        $hoy = new \DateTimeImmutable('today');

        return (int) $this->getEntityManager()->createQuery(
            'SELECT COUNT(DISTINCT IDENTITY(c.trastero))
             FROM App\Contrato\Domain\Model\Contrato c
             JOIN c.trastero t
             JOIN t.local l
             WHERE c.estado != :cancelado
             AND c.fechaInicio <= :hoy
             AND (c.fechaFin IS NULL OR c.fechaFin >= :hoy)
             AND c.deletedAt IS NULL
             AND t.deletedAt IS NULL
             AND l.id = :localId'
        )
            ->setParameter('cancelado', ContratoEstado::CANCELADO)
            ->setParameter('hoy', $hoy, 'date_immutable')
            ->setParameter('localId', $localId)
            ->getSingleScalarResult();
    }

    public function countTrasterosReservados(): int
    {
        $hoy = new \DateTimeImmutable('today');

        return (int) $this->getEntityManager()->createQuery(
            'SELECT COUNT(DISTINCT IDENTITY(c.trastero))
             FROM App\Contrato\Domain\Model\Contrato c
             JOIN c.trastero t
             WHERE c.estado != :cancelado
             AND c.fechaInicio > :hoy
             AND c.deletedAt IS NULL
             AND t.deletedAt IS NULL
             AND t.estado != :mantenimiento'
        )
            ->setParameter('cancelado', ContratoEstado::CANCELADO)
            ->setParameter('hoy', $hoy, 'date_immutable')
            ->setParameter('mantenimiento', 'mantenimiento')
            ->getSingleScalarResult();
    }

    public function count(array $criteria = []): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.deletedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
