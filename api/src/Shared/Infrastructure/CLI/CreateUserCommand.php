<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\CLI;

use App\Users\Application\Command\CreateUser\CreateUserCommand as CreateUserAppCommand;
use App\Users\Domain\Model\UserRole;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create a new user'
)]
final class CreateUserCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $commandBus
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('nombre', InputArgument::REQUIRED, 'User name')
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('password', InputArgument::REQUIRED, 'User password')
            ->addArgument('rol', InputArgument::OPTIONAL, 'User role (admin|gestor|readonly)', UserRole::ADMIN->value);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $rol = $input->getArgument('rol');
        if (!in_array($rol, array_column(UserRole::cases(), 'value'), true)) {
            $io->error(sprintf('Invalid role "%s". Valid roles: %s', $rol, implode(', ', array_column(UserRole::cases(), 'value'))));
            return Command::FAILURE;
        }

        $this->commandBus->dispatch(new CreateUserAppCommand(
            nombre: $input->getArgument('nombre'),
            email: $input->getArgument('email'),
            password: $input->getArgument('password'),
            rol: $rol,
            activo: true,
        ));

        $io->success(sprintf('User "%s" (%s) created successfully with role "%s".', $input->getArgument('nombre'), $input->getArgument('email'), $rol));

        return Command::SUCCESS;
    }
}
