<?php

namespace App\Tests\Serialization;

use App\Entity\User;
use App\Enum\RoleEnum;
use App\Tests\SerializationTestCase;
use DateTimeImmutable;

class UserSerializationTest extends SerializationTestCase
{
    public function testUserSerialization(): void
    {
        $user = (new User())
            ->setUuid('c4b637a6-0289-4fde-ac98-73d83d1f68f1')
            ->setEmail(strtolower('address@mail.fr'))
            ->setPlainPassword('plain-password')
            ->setPassword('encrypted-password')
            ->setPasswordResetToken('reset-password-token')
            ->addRole(RoleEnum::USER)
            ->addRole(RoleEnum::ADMIN)
            ->setIdentity(
                (new User\Identity())
                    ->setFirstName('John')
                    ->setLastName('Doe')
                    ->setNationality('FR')
                    ->setAnniversary(new DateTimeImmutable('2000-01-01'))
            )
            ->setEnable(true);

        $this->assertEquals(
            [
                'uuid'     => 'c4b637a6-0289-4fde-ac98-73d83d1f68f1',
                'roles'    => ['ROLE_USER', 'ROLE_ADMIN'],
                'identity' => [
                    'nationality' => 'FR',
                    'anniversary' => '2000-01-01',
                    'firstName'   => 'John',
                    'lastName'    => 'Doe'
                ]
            ],
            $this->toArray($user),
            'User serialization has changed'
        );
    }
}
