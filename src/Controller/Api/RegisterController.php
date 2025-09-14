<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Enum\RoleEnum;
use App\Form\Account\ApiRegisterType;
use App\Model\Form\FormErrorModel;
use App\Model\User\RegisterModel;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Tag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Security(name: null)]
#[Tag(name: 'Authentification')]
class RegisterController extends AbstractApiController
{
    /**
     * Check whether an e‑mail address is available for registration.
     */
    #[Route(
        path: '/auth/register/available',
        name: 'api_register_available',
        methods: ['POST']
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'username', type: 'string', example: 'john.doe@mail.fr')
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Email can be used'
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_ACCEPTABLE,
        description: 'Email already used'
    )]
    final public function registerAvailable(
        Request $request,
        UserRepository $userRepository,
    ): JsonResponse {
        $email   = $request->request->get('username');
        $user    = $userRepository->findOneByEmail($email);

        if ($user instanceof User) {
            return $this->messageResponse('Email already used', Response::HTTP_NOT_ACCEPTABLE);
        }

        return $this->messageResponse('Email can be used');
    }

    /**
     * Create a new user account.
     */
    #[Route(
        path: '/auth/register',
        name: 'api_register_user',
        methods: ['POST']
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            required: ['username', 'password'],
            properties: [
                new OA\Property(property: 'username', type: 'string', example: 'john.doe@mail.fr'),
                new OA\Property(property: 'password', type: 'string', format: 'password')
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'User created'
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Bad request',
        content: new OA\JsonContent(
            ref: FormErrorModel::class
        )
    )]
    final public function registerUser(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserService $userService
    ): JsonResponse {
        $data = new RegisterModel();
        $form = $this->handleJsonFormRequest(
            $request,
            ApiRegisterType::class,
            $data
        );

        if (!$form->isValid()) {
            return $this->formErrorResponse($form, Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->findOneBy(['email' => strtolower($data->getUsername())]);

        if ($user) {
            return $this->messageResponse('Email already exit', Response::HTTP_FORBIDDEN);
        }

        $user = $userService->createUser(
            $data->getUsername(),
            $data->getPassword()
        );
        $user->setRoles([RoleEnum::USER]);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json($user, Response::HTTP_CREATED);
    }
}
