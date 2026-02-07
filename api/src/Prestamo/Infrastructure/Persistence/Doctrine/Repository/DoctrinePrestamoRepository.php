<?php

declare(strict_types=1);

namespace App\Prestamo\Infrastructure\Persistence\Doctrine\Repository;

use App\Prestamo\Domain\Model\Prestamo;
use App\Prestamo\Domain\Model\PrestamoId;
use App\Prestamo\Domain\Repository\PrestamoRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Prestamo>
 */
final class DoctrinePrestamoRepository extends ServiceEntityRepository implements PrestamoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Prestamo::class);
    }

    public function save(Prestamo $prestamo): void
    {
        $this->getEntityManager()->persist($prestamo);
        $this->getEntityManager()->flush();
    }

    public function remove(Prestamo $prestamo): void
    {
        $this->getEntityManager()->remove($prestamo);
        $this->getEntityManager()->flush();
    }

    public function findById(PrestamoId $id): ?Prestamo
    {
        return $this->find($id->value);
    }

    /**
     * @return Prestamo[]
     */
    public function findAll(): array
    {
        return parent::findAll();
    }

    /**
     * @return Prestamo[]
     */
    public function findActivePrestamos(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.deletedAt IS NULL')
            ->orderBy('p.fechaConcesion', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Prestamo[]
     */
    public function findByLocalId(int $localId): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.local', 'l')
            ->where('l.id = :localId')
            ->andWhere('p.deletedAt IS NULL')
            ->setParameter('localId', $localId)
            ->orderBy('p.fechaConcesion', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Prestamo[]
     */
    public function findByEstado(string $estado): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.estado = :estado')
            ->andWhere('p.deletedAt IS NULL')
            ->setParameter('estado', $estado)
            ->orderBy('p.fechaConcesion', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Prestamo[]
     */
    public function findByEntidadBancaria(string $entidadBancaria): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.entidadBancaria LIKE :entidadBancaria')
            ->andWhere('p.deletedAt IS NULL')
            ->setParameter('entidadBancaria', '%' . $entidadBancaria . '%')
            ->orderBy('p.fechaConcesion', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function count(array $criteria = []): int
    {
        return parent::count($criteria);
    }

    public function getTotalADevolverByEstado(string $estado): float
    {
        $result = $this->createQueryBuilder('p')
            ->select('SUM(p.totalADevolver)')
            ->where('p.estado = :estado')
            ->andWhere('p.deletedAt IS NULL')
            ->setParameter('estado', $estado)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }
}
