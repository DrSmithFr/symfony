<?php

namespace App\Tests\Controller\Api;

use App\Tests\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class LoginControllerTest extends ApiTestCase
{
    public function testLoginWithUserNotFound(): void
    {
        $this->apiPost(
            '/api/auth/login',
            [
                'username' => 'bad_user',
                'password' => 'bad_password',
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testLoginBadCredencial(): void
    {
        $this->apiPost(
            '/api/auth/login',
            [
                'username' => 'admin@mail.com',
                'password' => 'bad_password',
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testLoginAdmin(): void
    {
        $this->apiPost(
            '/api/auth/login',
            [
                'username' => 'admin@mail.com',
                'password' => 'password',
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testLogin(): void
    {
        $this->apiPost(
            '/api/auth/login',
            [
                'username' => 'user@mail.com',
                'password' => 'password',
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testLoginUserDisable(): void
    {
        $this->apiPost(
            '/api/auth/login',
            [
                'username' => 'disable@mail.com',
                'password' => 'password',
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
