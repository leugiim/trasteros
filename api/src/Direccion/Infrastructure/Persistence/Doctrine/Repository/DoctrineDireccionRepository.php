<?php

declare(strict_types=1);

namespace App\Direccion\Infrastructure\Persistence\Doctrine\Repository;

use App\Direccion\Domain\Model\Direccion;
use App\Direccion\Domain\Model\DireccionId;
use App\Direccion\Domain\Repository\DireccionRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Direccion>
 */
final class DoctrineDireccionRepository extends ServiceEntityRepository implements DireccionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Direccion::class);
    }

    public function save(Direccion $direccion): void
    {
        $this->getEntityManager()->persist($direccion);
        $this->getEntityManager()->flush();
    }

    public function remove(Direccion $direccion): void
    {
        $this->getEntityManager()->remove($direccion);
        $this->getEntityManager()->flush();
    }

    public function findById(DireccionId $id): ?Direccion
    {
        return $this->find($id->value);
    }

    /**
     * @return Direccion[]
     */
    public function findAll(): array
    {
        return parent::findAll();
    }

    /**
     * @return Direccion[]
     */
    public function findActiveDirecciones(): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.deletedAt IS NULL')
            ->orderBy('d.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Direccion[]
     */
    public function findByCiudad(string $ciudad): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.ciudad = :ciudad')
            ->andWhere('d.deletedAt IS NULL')
            ->setParameter('ciudad', $ciudad)
            ->orderBy('d.nombreVia', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Direccion[]
     */
    public function findByProvincia(string $provincia): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.provincia = :provincia')
            ->andWhere('d.deletedAt IS NULL')
            ->setParameter('provincia', $provincia)
            ->orderBy('d.ciudad', 'ASC')
            ->addOrderBy('d.nombreVia', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Direccion[]
     */
    public function findByCodigoPostal(string $codigoPostal): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.codigoPostal = :codigoPostal')
            ->andWhere('d.deletedAt IS NULL')
            ->setParameter('codigoPostal', $codigoPostal)
            ->orderBy('d.nombreVia', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function count(array $criteria = []): int
    {
        return parent::count($criteria);
    }
}
