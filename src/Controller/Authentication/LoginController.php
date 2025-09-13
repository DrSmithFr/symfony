<?php

declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\AbstractApiController;
use App\Model\LoginModel;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use RuntimeException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @OA\Tag(name="Authentification")
 */
#[Security(name: null)]
class LoginController extends AbstractApiController
{
    /**
     * Initialise sessions with encryption API (Token valid for 1 hour)
     * @OA\RequestBody(@Model(type=LoginModel::class))
     * @OA\Response(
     *     response=200,
     *     description="User connected",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      @OA\Schema(type="object", example={"token": "xxxxxxx", "refresh_token": "xxxxxxx"})
     *    )
     * )
     * @OA\Response(response="401", description="Cannot connect user")
     */
    #[Route(path: '/api/auth/login', name: 'app_login', methods: ['post'])]
    final public function login(): never
    {
        throw new RuntimeException(
            'You may have screwed the firewall configuration, this function should not have been called.'
        );
    }

    /**
     * Initialise sessions with encryption API (Token valid for 1 hour)
     * @OA\RequestBody(
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="refresh_token", type="string", example="xxxxxxx")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="User reconnected",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      @OA\Schema(type="object", example={"token": "xxxxxxx", "refresh_token": "xxxxxxx"})
     *    )
     * )
     * @OA\Response(response="401", description="Cannot connect user")
     */
    #[Route(path: '/api/auth/refresh', name: 'app_login_refresh', methods: ['post'])]
    final public function loginRefresh(): never
    {
        throw new RuntimeException(
            'You may have screwed the firewall configuration, this function should not have been called.'
        );
    }
}
