<?php

declare(strict_types=1);

namespace App\Contrato\Application\Command\DeleteContrato;

use App\Contrato\Domain\Exception\ContratoNotFoundException;
use App\Contrato\Domain\Repository\ContratoRepositoryInterface;
use App\Users\Domain\Model\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeleteContratoCommandHandler
{
    public function __construct(
        private ContratoRepositoryInterface $contratoRepository,
        private Security $security
    ) {
    }

    public function __invoke(DeleteContratoCommand $command): void
    {
        $contrato = $this->contratoRepository->findById($command->id);
        if ($contrato === null) {
            throw new ContratoNotFoundException($command->id);
        }

        /** @var User|null $user */
        $user = $this->security->getUser();

        if ($user !== null) {
            $contrato->softDelete($user);
        }

        $this->contratoRepository->save($contrato);
    }
}
