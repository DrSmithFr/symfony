<?php

declare(strict_types=1);

namespace App\Form\Account;

use App\Model\Password\PasswordResetModel;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AppPasswordResetType extends AbstractType
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('token', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type'            => PasswordType::class,
                'first_options'   => [
                    'toggle' => true,
                    'label' => 'Mot de passe',
                    'attr'  => ['placeholder' => '********'],
                ],
                'second_options'  => [
                    'toggle' => true,
                    'label' => 'Répéter le mot de passe',
                    'attr'  => ['placeholder' => '********'],
                ],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'required'        => true,
                'constraints'     => [
                    new Length([
                        'min'        => 8,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Change password',
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var PasswordResetModel $data */
            $data = $event->getData();
            $existingUser = $this->userRepository->getUserByPasswordResetToken($data->getToken());

            if (!$existingUser) {
                $event
                    ->getForm()
                    ->get('token')
                    ->addError(
                        new FormError('Invalid token')
                    );
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => PasswordResetModel::class,
            'csrf_protection' => false,
        ]);
    }
}
