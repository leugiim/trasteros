<?php

declare(strict_types=1);

namespace App\Local\Infrastructure\Persistence\Doctrine\Repository;

use App\Local\Domain\Model\Local;
use App\Local\Domain\Model\LocalId;
use App\Local\Domain\Repository\LocalRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Local>
 */
final class DoctrineLocalRepository extends ServiceEntityRepository implements LocalRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Local::class);
    }

    public function save(Local $local): void
    {
        $this->getEntityManager()->persist($local);
        $this->getEntityManager()->flush();
    }

    public function remove(Local $local): void
    {
        $this->getEntityManager()->remove($local);
        $this->getEntityManager()->flush();
    }

    public function findById(LocalId $id): ?Local
    {
        return $this->find($id->value);
    }

    /**
     * @return Local[]
     */
    public function findAll(): array
    {
        return parent::findAll();
    }

    /**
     * @return Local[]
     */
    public function findActiveLocales(): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.deletedAt IS NULL')
            ->orderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Local[]
     */
    public function findByNombre(string $nombre): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.nombre LIKE :nombre')
            ->andWhere('l.deletedAt IS NULL')
            ->setParameter('nombre', '%' . $nombre . '%')
            ->orderBy('l.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Local[]
     */
    public function findByDireccionId(int $direccionId): array
    {
        return $this->createQueryBuilder('l')
            ->join('l.direccion', 'd')
            ->where('d.id = :direccionId')
            ->andWhere('l.deletedAt IS NULL')
            ->setParameter('direccionId', $direccionId)
            ->orderBy('l.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function count(array $criteria = []): int
    {
        return parent::count($criteria);
    }
}
