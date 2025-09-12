<?php

namespace App\DataFixtures;

use App\Enum\RoleEnum;
use App\Service\UserService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    final public const REFERENCE_ADMIN = 'app-admin';
    final public const REFERENCE_USER = 'app-common';
    final public const REFERENCE_DISABLED = 'app-user-disabled';

    public function __construct(private readonly UserService $userService)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = $this->userService->createUser('admin@mail.com', 'password');
        $user = $this->userService->createUser('user@mail.com', 'password');
        $disabled = $this->userService->createUser('disable@mail.com', 'password');

        $this->setReference(self::REFERENCE_ADMIN, $admin);
        $this->setReference(self::REFERENCE_USER, $user);
        $this->setReference(self::REFERENCE_DISABLED, $disabled);

        $admin->setEnable(true);
        $user->setEnable(true);
        $disabled->setEnable(false);

        $admin->addRole(RoleEnum::ADMIN);
        $user->addRole(RoleEnum::USER);
        $disabled->addRole(RoleEnum::USER);

        $manager->persist($admin);
        $manager->persist($user);
        $manager->persist($disabled);

        $manager->flush();
    }
}
