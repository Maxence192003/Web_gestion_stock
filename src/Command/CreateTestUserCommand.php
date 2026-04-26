<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-test-user',
    description: 'Create or update the default test user for JWT authentication.',
)]
final class CreateTestUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = 'test@gmail.com';
        $plainPassword = 'test1234';

        $user = $this->userRepository->findOneBy(['email' => $email]);
        $isNewUser = null === $user;

        if ($isNewUser) {
            $user = new User();
            $this->entityManager->persist($user);
        }

        $user
            ->setNom('test')
            ->setPrenom('test')
            ->setEmail($email)
            ->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

        $this->entityManager->flush();

        $io->success(sprintf(
            'Utilisateur de test %s : %s / %s',
            $isNewUser ? 'cree' : 'mis a jour',
            $email,
            $plainPassword,
        ));

        return Command::SUCCESS;
    }
}
