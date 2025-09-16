<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Form\Password\PasswordResetType;
use App\Model\Password\PasswordResetModel;
use App\Repository\UserRepository;
use App\Service\MailerService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Tag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Tag(name: 'Authentification')]
class PasswordController extends AbstractApiController
{
    /**
     * Request a password reset token (by mail).
     *
     * @throws NonUniqueResultException
     * @throws TransportExceptionInterface
     */
    #[Route(path: '/auth/recover', name: 'api_reset_password_recover', methods: ['post'])]
    #[OA\RequestBody(
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                example: ['username' => 'user@mail.com']
            )
        )
    )]
    #[OA\Response(
        response: Response::HTTP_ACCEPTED,
        description: 'Mail with reset token sent'
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'User not found'
    )]
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
     */
    #[Route(path: '/auth/reset_password', name: 'api_reset_password', methods: ['patch'])]
    #[OA\RequestBody(content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'token', type: 'string'),
            new OA\Property(property: 'password', type: 'string'),
        ]
    ))]
    #[OA\Response(
        response: Response::HTTP_ACCEPTED,
        description: 'User password updated'
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'New password not valid'
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Token not found'
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_ACCEPTABLE,
        description: 'Token expired'
    )]
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
     */
    #[Route(path: '/auth/reset_password/validity', name: 'api_reset_password_token_validity', methods: ['post'])]
    #[OA\RequestBody(
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                type: 'object',
                example: ['token' => '9E4PrHk1sHLCs4ruM3k7v-mgGNWdecm9yhi1RLZ491k']
            )
        )
    )]
    #[OA\Response(
        response: Response::HTTP_ACCEPTED,
        description: 'Reset password token still valid'
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Reset password token outdated or invalid'
    )]
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
