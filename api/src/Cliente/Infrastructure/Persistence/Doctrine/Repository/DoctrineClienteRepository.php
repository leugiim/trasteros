<?php

declare(strict_types=1);

namespace App\Cliente\Infrastructure\Persistence\Doctrine\Repository;

use App\Cliente\Domain\Model\Cliente;
use App\Cliente\Domain\Model\ClienteId;
use App\Cliente\Domain\Repository\ClienteRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cliente>
 */
final class DoctrineClienteRepository extends ServiceEntityRepository implements ClienteRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cliente::class);
    }

    public function save(Cliente $cliente): void
    {
        $this->getEntityManager()->persist($cliente);
        $this->getEntityManager()->flush();
    }

    public function remove(Cliente $cliente): void
    {
        $this->getEntityManager()->remove($cliente);
        $this->getEntityManager()->flush();
    }

    public function findById(ClienteId $id): ?Cliente
    {
        return $this->find($id->value);
    }

    public function findByDniNie(string $dniNie): ?Cliente
    {
        return $this->createQueryBuilder('c')
            ->where('c.dniNie = :dniNie')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('dniNie', strtoupper(trim($dniNie)))
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByEmail(string $email): ?Cliente
    {
        return $this->createQueryBuilder('c')
            ->where('c.email = :email')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('email', strtolower(trim($email)))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Cliente[]
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.deletedAt IS NULL')
            ->orderBy('c.apellidos', 'ASC')
            ->addOrderBy('c.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Cliente[]
     */
    public function findActivos(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.activo = :activo')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('activo', true)
            ->orderBy('c.apellidos', 'ASC')
            ->addOrderBy('c.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Cliente[]
     */
    public function searchByNombreOrApellidos(string $searchTerm): array
    {
        $searchTerm = trim($searchTerm);

        return $this->createQueryBuilder('c')
            ->where('c.nombre LIKE :searchTerm OR c.apellidos LIKE :searchTerm')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('c.apellidos', 'ASC')
            ->addOrderBy('c.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function existsByDniNie(string $dniNie, ?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.dniNie = :dniNie')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('dniNie', strtoupper(trim($dniNie)));

        if ($excludeId !== null) {
            $qb->andWhere('c.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function existsByEmail(string $email, ?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.email = :email')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('email', strtolower(trim($email)));

        if ($excludeId !== null) {
            $qb->andWhere('c.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function count(array $criteria = []): int
    {
        return parent::count($criteria);
    }
}
