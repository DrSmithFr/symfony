<?php

namespace App\Controller\App;

use App\Enum\RoleEnum;
use App\Form\Account\AppPasswordResetType;
use App\Form\Account\AppRecoverAccountType;
use App\Form\Account\AppRegisterType;
use App\Model\Password\PasswordResetModel;
use App\Model\User\RecoverAccountModel;
use App\Model\User\RegisterModel;
use App\Repository\UserRepository;
use App\Service\MailerService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AccountController extends AbstractController
{
    #[Route('/account/login', name: 'app_login')]
    public function loginAction(
        AuthenticationUtils $authenticationUtils,
    ): Response {
        return $this->render('account/login.html.twig', [
            'error'                => $authenticationUtils->getLastAuthenticationError(),
            'last_username'        => $authenticationUtils->getLastUsername(),
            'page_title'           => 'Welcome to Skeleton',


            // the string used to generate the CSRF token. If you don't define
            // this parameter, the login form won't include a CSRF token
            'csrf_token_intention' => 'authenticate',

            // the URL users are redirected to after the login (default: '/admin')
            'target_path'          => $this->generateUrl('admin_dashboard'),

            // whether to enable or not the "remember me" checkbox (default: false)
            'remember_me_enabled'  => true,
            'remember_me_checked'  => false,

            'forgot_password_enabled' => true,
        ]);
    }

    #[Route('/account/register', name: 'app_register')]
    public function registerAction(
        Request $request,
        UserService $userService,
        EntityManagerInterface $entityManager,
        AuthenticationUtils $authenticationUtils,
        Security $security,
    ): Response {
        $data = new RegisterModel();

        if ($username = $authenticationUtils->getLastUsername()) {
            $data->setUsername($username);
        }

        $form = $this->createForm(AppRegisterType::class, $data);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userService->createUser(
                $data->getUsername(),
                $data->getPassword()
            );

            $user->setRoles([RoleEnum::USER]);

            $entityManager->persist($user);
            $entityManager->flush();

            $security->login($user);

            return $this->redirect('/');
        }

        return $this->render('account/register.html.twig', [
            'error'         => null,
            'last_username' => $authenticationUtils->getLastUsername(),
            'form'          => $form,
            'page_title'    => 'Register to Skeleton',
        ]);
    }

    #[Route('/account/recover', name: 'app_recover')]
    public function recoverAction(
        Request $request,
        AuthenticationUtils $authenticationUtils,
        UserService $userService,
        UserRepository $userRepository,
        MailerService $mailerService,
        EntityManagerInterface $entityManager,
    ): Response {
        $data = new RecoverAccountModel();

        if ($username = $authenticationUtils->getLastUsername()) {
            $data->setUsername($username);
        }

        $form = $this->createForm(AppRecoverAccountType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userRepository->findOneByEmail($data->getUsername());

            if (!$user) {
                throw new RuntimeException('User not found');
            }

            $userService->generateResetToken($user);
            $mailerService->sendResetPasswordMail($user);

            $entityManager->flush();

            $this->addFlash('info', 'Mail sent');

            return $this->redirectToRoute('app_reset_password');
        }

        return $this->render('account/recover.html.twig', [
            'error'         => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),

            'form' => $form,

            'page_title'  => 'Forgot your Password?',
            'target_path' => $this->generateUrl('admin_dashboard'),
        ]);
    }

    #[Route('/account/reset_password', name: 'app_reset_password')]
    public function resetPasswordAction(
        Request $request,
        AuthenticationUtils $authenticationUtils,
        UserRepository $userRepository,
        UserService $userService,
        EntityManagerInterface $entityManager,
        Security $security,
    ): Response {
        $data = new PasswordResetModel();

        if ($token = $request->query->get('token')) {
            $data->setToken($token);
        }

        $form = $this->createForm(AppPasswordResetType::class, $data);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userRepository->getUserByPasswordResetToken($data->getToken());

            if (!$user) {
                throw new RuntimeException('User not found');
            }

            $user->setPlainPassword($data->getPassword());

            $userService->updatePassword($user);
            $userService->clearPasswordResetToken($user);

            $entityManager->flush();
            $security->login($user);

            return $this->redirect('/');
        }

        return $this->render('account/reset_password.html.twig', [
            'error'         => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),

            'form' => $form,

            'page_title'  => 'Change your Password!',
            'target_path' => $this->generateUrl('admin_dashboard'),
        ]);
    }

    #[Route('/account/logout', name: 'app_logout')]
    public function logoutAction(): Response
    {
        throw new RuntimeException('This should never be called directly.');
    }
}
