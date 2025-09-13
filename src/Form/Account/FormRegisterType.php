<?php

declare(strict_types=1);

namespace App\Form\Account;

use App\Model\User\RegisterModel;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class FormRegisterType extends AbstractType
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
            ->add('password', RepeatedType::class, [
                'type'            => PasswordType::class,
                'first_options'   => [
                    'label' => 'Mot de passe',
                    'attr'  => ['placeholder' => '********'],
                ],
                'second_options'  => [
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
                'label' => 'Create Account',
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var RegisterModel $data */
            $data = $event->getData();
            $existingUser = $this->userRepository->findOneByEmail($data->getUsername());

            if ($existingUser) {
                $event
                    ->getForm()
                    ->get('username')
                    ->addError(
                        new FormError('Cet e‑mail est déjà utilisé')
                    );
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => RegisterModel::class,
            'csrf_protection' => false,
        ]);
    }
}
