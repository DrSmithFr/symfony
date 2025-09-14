<?php

declare(strict_types=1);

namespace App\Form\Account;

use App\Model\User\RecoverAccountModel;
use App\Model\User\RegisterModel;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppRecoverAccountType extends AbstractType
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', EmailType::class, [
                'label'    => 'Email',
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Send recovery email',
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var RegisterModel $data */
            $data = $event->getData();
            $existingUser = $this->userRepository->findOneByEmail($data->getUsername());

            if (!$existingUser) {
                $event
                    ->getForm()
                    ->get('username')
                    ->addError(
                        new FormError('Cet utilisateur n\'existe pas !')
                    );
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => RecoverAccountModel::class,
            'csrf_protection' => false,
        ]);
    }
}
