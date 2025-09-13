<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\AbstractApiController;
use App\Entity\User;
use App\Entity\User\Identity;
use App\Form\Account\IdentityType;
use App\Form\Password\PasswordUpdateType;
use App\Model\Password\PasswordUpdateModel;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @OA\Tag(name="Users Management")
 */
#[
    Route('api/user', name: 'api_user'),
    Security(name: null)
]
class UserController extends AbstractApiController
{
    /**
     * @OA\Response(
     *     response=201,
     *     description="User created",
     *     @Model(type=User::class)
     * )
     * @OA\Response(response="403", description="No user connected")
     */
    #[Route(path: '/information', name: '_information', methods: ['get'])]
    public function currentUser(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->messageResponse('not connected', Response::HTTP_FORBIDDEN);
        }

        return $this->serializeResponse($this->getUser());
    }

    /**
     * Update the current user password.
     * @OA\RequestBody(@Model(type=PasswordUpdateModel::class))
     * @OA\Response(response=202, description="Update User password")
     * @OA\Response(response=400, description="New password not valid")
     * @OA\Response(response=403, description="Current password not valid")
     */
    #[Route(path: '/password_update', name: '_password_update', methods: ['patch'])]
    public function passwordResetAction(
        Request $request,
        UserService $userService,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = new PasswordUpdateModel();

        $form = $this->handleJsonFormRequest(
            $request,
            PasswordUpdateType::class,
            $data
        );

        /** @var User $user */
        $user = $this->getUser();

        if (!$userService->isPasswordValid($user, $data->getCurrentPassword())) {
            return $this->messageResponse(
                'Current password not valid',
                Response::HTTP_FORBIDDEN
            );
        }

        if (!$form->isValid()) {
            return $this->formErrorResponse($form, Response::HTTP_BAD_REQUEST);
        }

        $user->setPlainPassword(
            $data->getNewPassword()
        );

        $userService->updatePassword($user);
        $entityManager->flush();

        return $this->messageResponse('Password changed', Response::HTTP_ACCEPTED);
    }

    /**
     * Update the current user identity.
     * @OA\RequestBody(@Model(type=Identity::class))
     * @OA\Response(response=202, description="Update User Identity")
     * @OA\Response(response=400, description="Identity not valid")
     */
    #[Route(path: '/identity', name: '_identity_update', methods: ['put'])]
    public function updateIdentityAction(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = new Identity();

        $form = $this->handleJsonFormRequest(
            $request,
            IdentityType::class,
            $data
        );

        if (!$form->isValid()) {
            return $this->formErrorResponse($form, Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = $this->getUser();

        $user->setIdentity($data);
        $entityManager->flush();

        return $this->serializeResponse($data, ['Default'], Response::HTTP_ACCEPTED);
    }
}
