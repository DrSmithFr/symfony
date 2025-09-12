<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\SerializerAware;
use App\Entity\User;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractApiController extends AbstractController
{
    use SerializerAware;

    /**
     * UserController constructor.
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->setSerializer($serializer);
    }

    public function getUser(): ?User
    {
        $user = parent::getUser();

        if ($user instanceof User) {
            return $user;
        }

        return null;
    }

    /**
     * Creates and returns a Form instance from the type of the form.
     */
    protected function handleJsonFormRequest(Request $request, string $type, mixed $data = null): FormInterface
    {
        return $this
            ->createForm(
                $type,
                $data
            )
            ->submit($request->request->all());
    }
}
