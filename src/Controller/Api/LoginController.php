<?php
declare(strict_types=1);

namespace App\Controller\Api;

use OpenApi\Attributes as OA;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Tag(name: 'Authentification')]
class LoginController extends AbstractApiController
{
    /**
     * Initialise sessions with encryption API (Token valid for 1 hour)
     */
    #[Route(path: '/auth/login', name: 'api_login', methods: ['post'])]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'password', type: 'string'),
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'User connected',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                example: ['token' => 'xxxxxxx', 'refresh_token' => 'xxxxxxx']
            )
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Cannot connect user'
    )]
    final public function login(): never
    {
        throw new RuntimeException(
            'You may have screwed the firewall configuration, this function should not have been called.'
        );
    }

    /**
     * Initialise sessions with encryption API (Token valid for 1 hour)
     */
    #[Route(path: '/auth/refresh', name: 'api_login_refresh', methods: ['post'])]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'refresh_token', type: 'string', example: 'xxxxxxx'),
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'User reconnected',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                example: [
                    'token'         => 'xxxxxxx',
                    'refresh_token' => 'xxxxxxx',
                ]
            )
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Cannot connect user'
    )]
    final public function loginRefresh(): never
    {
        throw new RuntimeException(
            'You may have screwed the firewall configuration, this function should not have been called.'
        );
    }
}
