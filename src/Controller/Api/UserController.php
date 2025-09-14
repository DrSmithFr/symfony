<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Entity\User\Identity;
use App\Form\Account\IdentityType;
use App\Form\Password\PasswordUpdateType;
use App\Model\Password\PasswordUpdateModel;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user', name: 'api_user')]
#[OA\Tag(name: 'Users Management')]
#[Security(name: null)]
class UserController extends AbstractApiController
{
    /**
     * Récupère l'utilisateur actuellement connecté.
     */
    #[Route(path: '/information', name: '_information', methods: ['GET'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'User created',
        attachables: [new Model(type: User::class)]
    )]
    #[OA\Response(
        response: Response::HTTP_FORBIDDEN,
        description: 'No user connected'
    )]
    public function currentUser(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->messageResponse('not connected', Response::HTTP_FORBIDDEN);
        }

        return $this->serializeResponse($this->getUser());
    }

    /**
     * Met à jour le mot de passe de l'utilisateur connecté.
     */
    #[Route(path: '/password_update', name: '_password_update', methods: ['PATCH'])]
    #[OA\RequestBody(
        attachables: [new Model(type: PasswordUpdateModel::class)]
    )]
    #[OA\Response(
        response: Response::HTTP_ACCEPTED,
        description: 'Update User password'
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'New password not valid'
    )]
    #[OA\Response(
        response: Response::HTTP_FORBIDDEN,
        description: 'Current password not valid'
    )]
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

        $user->setPlainPassword($data->getNewPassword());

        $userService->updatePassword($user);
        $entityManager->flush();

        return $this->messageResponse('Password changed', Response::HTTP_ACCEPTED);
    }

    /**
     * Met à jour les informations d'identité de l'utilisateur.
     */
    #[Route(path: '/identity', name: '_identity_update', methods: ['PUT'])]
    #[OA\RequestBody(
        attachables: [new Model(type: Identity::class)]
    )]
    #[OA\Response(
        response: Response::HTTP_ACCEPTED,
        description: 'Update User Identity'
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Identity not valid'
    )]
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
