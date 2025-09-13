<?php

declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\AbstractApiController;
use App\Entity\User;
use App\Enum\RoleEnum;
use App\Form\User\RegisterType;
use App\Model\Form\FormErrorModel;
use App\Model\User\RegisterModel;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Annotations as OA;
use OpenApi\Attributes\Tag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[
    Security(name: null),
    Tag(name: 'Authentification'),
]
class RegisterController extends AbstractApiController
{
    /**
     * Create a new account
     * @OA\RequestBody(
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="username", type="string", example="john.doe@mail.fr")
     *     )
     * )
     * @OA\Response(response=200, description="Email can be used")
     * @OA\Response(response="406", description="Email already used")
     */
    #[Route(
        path: '/api/auth/register/available',
        name: 'api_register_available',
        methods: ['post']
    )]
    final public function registerAvailable(
        Request $request,
        UserRepository $userRepository,
    ): JsonResponse {
        $email = $request->request->get('username');
        $user = $userRepository->findOneByEmail($email);

        if ($user instanceof User) {
            return $this->messageResponse('Email already used', Response::HTTP_NOT_ACCEPTABLE);
        }

        return $this->messageResponse('Email can be used');
    }

    /**
     * Create a new account
     * @OA\RequestBody(@Model(type=RegisterModel::class))
     * @OA\Response(response=201, description="User created")
     * @OA\Response(
     *     response="400",
     *     description="Bad request",
     *     @Model(type=FormErrorModel::class)
     * )
     */
    #[Route(
        path: '/api/auth/register',
        name: 'api_register_user',
        methods: ['post']
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
            RegisterType::class,
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
