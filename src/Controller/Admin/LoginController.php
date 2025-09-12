<?php

namespace App\Controller\Admin;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AdminPageController
{
    #[Route('/admin/login', name: 'admin_login')]
    public function loginAction(
        AuthenticationUtils $authenticationUtils,
    ): Response {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@EasyAdmin/page/login.html.twig', [
            // parameters usually defined in Symfony login forms
            'error' => $error,
            'last_username' => $lastUsername,

            'page_title' => 'Skeleton',

            // the string used to generate the CSRF token. If you don't define
            // this parameter, the login form won't include a CSRF token
            'csrf_token_intention' => 'authenticate',

            // the URL users are redirected to after the login (default: '/admin')
            'target_path' => $this->generateUrl('admin_dashboard'),

            // whether to enable or not the "remember me" checkbox (default: false)
            'remember_me_enabled' => true,
            'remember_me_checked' => false,
        ]);
    }

    #[Route('/admin/logout', name: 'admin_logout')]
    public function logoutAction(): Response
    {
        throw new RuntimeException('This should never be called directly.');
    }
}
