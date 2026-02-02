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

    public function findByEstado(ContratoEstado $estado): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.estado = :estado')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('estado', $estado)
            ->orderBy('c.fechaInicio', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findContratosActivosByCliente(int $clienteId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.cliente = :clienteId')
            ->andWhere('c.estado = :estado')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('clienteId', $clienteId)
            ->setParameter('estado', ContratoEstado::ACTIVO)
            ->orderBy('c.fechaInicio', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findContratosActivosByTrastero(int $trasteroId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.trastero = :trasteroId')
            ->andWhere('c.estado = :estado')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('trasteroId', $trasteroId)
            ->setParameter('estado', ContratoEstado::ACTIVO)
            ->orderBy('c.fechaInicio', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function hasContratoActivoTrastero(int $trasteroId): bool
    {
        $count = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.trastero = :trasteroId')
            ->andWhere('c.estado = :estado')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('trasteroId', $trasteroId)
            ->setParameter('estado', ContratoEstado::ACTIVO)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function findOneContratoActivoByTrastero(int $trasteroId): ?Contrato
    {
        return $this->createQueryBuilder('c')
            ->where('c.trastero = :trasteroId')
            ->andWhere('c.estado = :estado')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('trasteroId', $trasteroId)
            ->setParameter('estado', ContratoEstado::ACTIVO)
            ->orderBy('c.fechaInicio', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Contrato[]
     */
    public function findProximosAVencer(int $dias = 30): array
    {
        $hoy = new \DateTimeImmutable();
        $limite = $hoy->modify("+{$dias} days");

        return $this->createQueryBuilder('c')
            ->where('c.estado = :estado')
            ->andWhere('c.fechaFin IS NOT NULL')
            ->andWhere('c.fechaFin BETWEEN :hoy AND :limite')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('estado', ContratoEstado::ACTIVO)
            ->setParameter('hoy', $hoy)
            ->setParameter('limite', $limite)
            ->orderBy('c.fechaFin', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Contrato[]
     */
    public function findConFianzaPendiente(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.estado = :estado')
            ->andWhere('c.fianzaPagada = false')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('estado', ContratoEstado::ACTIVO)
            ->orderBy('c.fechaInicio', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function remove(Contrato $contrato): void
    {
        $this->getEntityManager()->remove($contrato);
        $this->getEntityManager()->flush();
    }

    public function countByEstado(ContratoEstado $estado): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.estado = :estado')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('estado', $estado)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function count(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.deletedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
