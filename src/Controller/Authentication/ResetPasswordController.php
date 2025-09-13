<?php

declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\AbstractApiController;
use App\Entity\User;
use App\Form\Password\PasswordResetType;
use App\Model\Password\PasswordResetModel;
use App\Repository\UserRepository;
use App\Service\MailerService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Annotations as OA;
use OpenApi\Attributes\Tag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[
    Security(name: null),
    Tag(name: 'Authentification')
]
class ResetPasswordController extends AbstractApiController
{
    /**
     * Request a password reset token (by mail).
     * @OA\RequestBody(
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      @OA\Schema(
     *         type="object",
     *         example={"username": "user@mail.com"}
     *      )
     *    )
     * )
     * @OA\Response(response=202, description="Mail with reset token sent")
     * @OA\Response(response=404, description="User not found")
     *
     * @throws NonUniqueResultException
     * @throws TransportExceptionInterface
     */
    #[Route(path: '/api/auth/recover', name: 'reset_password_recover', methods: ['post'])]
    public function passwordResetRequestAction(
        Request $request,
        UserRepository $userRepository,
        UserService $userService,
        EntityManagerInterface $entityManager,
        MailerService $mailerService
    ): JsonResponse {
        $username = $request->get('username');
        $user = $userRepository->findOneByEmail($username);

        if (null === $user) {
            return $this->messageResponse('User not recognised', Response::HTTP_NOT_FOUND);
        }

        $userService->generateResetToken($user);
        $mailerService->sendResetPasswordMail($user);

        $entityManager->flush();

        return $this->messageResponse('mail sent', Response::HTTP_ACCEPTED);
    }

    /**
     * Reset password with token.
     * @OA\RequestBody(@Model(type=PasswordResetModel::class))
     * @OA\Response(response=202, description="User password updated")
     * @OA\Response(response=400, description="New password not valid")
     * @OA\Response(response=404, description="Token not found")
     * @OA\Response(response=406, description="Token expired")
     */
    #[Route(path: '/api/auth/reset_password', name: 'api_reset_password', methods: ['patch'])]
    public function passwordResetAction(
        Request $request,
        UserRepository $userRepository,
        UserService $userService,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = new PasswordResetModel();

        $form = $this->handleJsonFormRequest(
            $request,
            PasswordResetType::class,
            $data
        );

        if (!$form->isValid()) {
            return $this->formErrorResponse($form, Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = $userRepository->getUserByPasswordResetToken($data->getToken());

        if (null === $user) {
            return $this->messageResponse('token not found', Response::HTTP_NOT_FOUND);
        }

        if (!$userService->isTokenValid($user, $data->getToken())) {
            return $this->messageResponse('token expired', Response::HTTP_NOT_ACCEPTABLE);
        }

        $user->setPlainPassword($data->getPassword());

        $userService->updatePassword($user);
        $userService->clearPasswordResetToken($user);

        $entityManager->flush();

        return $this->messageResponse('Password changed', Response::HTTP_ACCEPTED);
    }

    /**
     * Check if reset password token is valid.
     * @OA\RequestBody(
     *   @OA\MediaType(
     *     mediaType="application/json",
     *     @OA\Schema(type="object", example={"token": "9E4PrHk1sHLCs4ruM3k7v-mgGNWdecm9yhi1RLZ491k"})
     *   )
     * )
     * @OA\Response(response=202, description="Reset password token still valid")
     * @OA\Response(response=404, description="Reset password token outdated or invalid")
     */
    #[Route(path: '/api/auth/reset_password/validity', name: 'api_reset_password_token_validity', methods: ['post'])]
    public function isPasswordResetTokenValidAction(
        Request $request,
        UserRepository $userRepository
    ): JsonResponse {
        $token = $request->get('token');
        $user = $userRepository->getUserByPasswordResetToken($token);

        if (null === $user) {
            return $this->messageResponse('token not valid.', Response::HTTP_NOT_FOUND);
        }

        return $this->messageResponse('token valid.', Response::HTTP_ACCEPTED);
    }
}
