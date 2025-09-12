<?php

namespace App\Command;

use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateUserCommand extends Command
{
    public function __construct(
        private readonly UserService $userService,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setName('app:user:create')
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'Email of user'
            )
            ->addArgument(
                'password',
                InputArgument::OPTIONAL,
                'Password of user'
            )
            ->addOption(
                'passwordLength',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Length of password',
                16
            );
    }

    /**
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Generating new user');

        $email = $input->getArgument('email');

        $errors = $this->validator->validate($email, new Email());

        if ($errors->count()) {
            $io->error($errors[0]->getMessage());
            return Command::FAILURE;
        }

        if (!$password = $input->getArgument('password')) {
            $password = $this->generatePassword(
                (int) $input->getOption('passwordLength')
            );
        }

        $user = $this->userService->createUser($email, $password);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->table(
            [
                ['uuid', 'password'],
            ],
            [
                [$user->getEmail(), $password],
            ]
        );

        $io->comment('Connexion allowed, but it will need permission promotion to use every endpoint');
        return Command::SUCCESS;
    }

    /**
     * @param int $length
     *
     * @return string
     * @throws Exception
     */
    private function generatePassword(int $length): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' .
            '0123456789-=~!@#$%^&*()_+,.<>?;:[]{}';

        $password = '';
        $max = strlen($chars) - 1;

        for ($index = 0; $index < $length; $index++) {
            $password .= $chars[random_int(0, $max)];
        }

        return $password;
    }
}
