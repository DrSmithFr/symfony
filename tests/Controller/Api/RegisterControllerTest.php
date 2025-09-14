<?php

namespace App\Tests\Controller\Api;

use App\Entity\User;
use App\Enum\RoleEnum;
use App\Tests\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class RegisterControllerTest extends ApiTestCase
{
    public function testRegisterUserWithBadEmail(): void
    {
        $this->apiPost(
            '/api/auth/register',
            [
                'username' => 'not_an_email',
                'password' => 'password',
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testRegisterUserWithShortPassword(): void
    {
        $this->apiPost(
            '/api/auth/register',
            [
                'username' => 'test-short-password@mail.com',
                'password' => '...',
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testRegisterUserEmailAlreadyUsed(): void
    {
        $this->apiPost(
            '/api/auth/register',
            [
                'username' => 'user@mail.com',
                'password' => 'password',
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testRegisterValid(): void
    {
        $this->apiPost(
            '/api/auth/register',
            [
                'username' => 'test@mail.com',
                'password' => 'password',
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $repository = self::getContainer()
                          ->get('doctrine')
                          ->getRepository(User::class);

        /** @var User $user */
        $user = $repository->findOneByEmail('test@mail.com');

        $this->assertEquals([RoleEnum::USER->value], $user->getRoles());
    }
}
