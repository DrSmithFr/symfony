<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Service\UserService;
use App\Tests\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends ApiTestCase
{
    public function testPasswordUpdateUnconnected(): void
    {
        $this->apiPatch(
            '/user/password_update',
            [
                'currentPassword' => 'password',
                'newPassword'     => 'new-password',
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testPasswordUpdateWithBadCurrentPassword()
    {
        $repository = self::getContainer()
                          ->get('doctrine')
                          ->getRepository(User::class);

        /** @var User $user */
        $user = $repository->findOneByEmail('user@mail.com');

        // simulate user connection
        $this->loginApiUser($user);

        $this->apiPatch(
            '/user/password_update',
            [
                'currentPassword' => 'bad-current-password',
                'newPassword'     => 'new-password',
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        // remove simulate user connection
        $this->disconnectUser();
    }

    public function testPasswordUpdateWithTooSmallNewPassword(): void
    {
        $this->apiPost(
            '/auth/register',
            [
                'username' => 'password-update-too-short@mail.com',
                'password' => 'password',
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $repository = self::getContainer()
                          ->get('doctrine')
                          ->getRepository(User::class);

        /** @var User $user */
        $user = $repository->findOneByEmail('password-update-too-short@mail.com');

        // simulate user connection
        $this->loginApiUser($user);

        $this->apiPatch(
            '/user/password_update',
            [
                'currentPassword' => 'password',
                'newPassword'     => '...',
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        // remove simulate user connection
        $this->disconnectUser();
    }

    public function testPasswordUpdateValid(): void
    {
        $this->apiPost(
            '/auth/register',
            [
                'username' => 'update-password-valid@mail.com',
                'password' => 'password',
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $repository = self::getContainer()
                          ->get('doctrine')
                          ->getRepository(User::class);

        /** @var User $user */
        $user = $repository->findOneByEmail('update-password-valid@mail.com');

        // simulate user connection
        $this->loginApiUser($user);

        $this->apiPatch(
            '/user/password_update',
            [
                'currentPassword' => 'password',
                'newPassword'     => 'new-password',
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_ACCEPTED);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        /** @var User $user */
        $user = $repository->findOneByEmail('update-password-valid@mail.com');
        $userService = self::getContainer()->get(UserService::class);

        // test if password has been changed
        $this->assertTrue(
            $userService->isPasswordValid($user, 'new-password'),
            'Password has not been changed'
        );

        // remove simulate user connection
        $this->disconnectUser();
    }

    public function testUpdateIdentity()
    {
        $repository = self::getContainer()
                          ->get('doctrine')
                          ->getRepository(User::class);

        /** @var User $user */
        $user = $repository->findOneByEmail('user@mail.com');

        // simulate user connection
        $this->loginApiUser($user);

        $this->apiPut(
            '/user/identity',
            $data = [
                "anniversary" => "1992-10-06",
                "firstName"   => "bob",
                "lastName"    => "moran",
                "nationality" => "fr",
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_ACCEPTED);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertEquals($data, $this->getApiResponse(), 'Identity has not been updated');
    }
}
