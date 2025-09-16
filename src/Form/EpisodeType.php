<?php

declare(strict_types = 1);

namespace App\Form;

use App\Entity\Episode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class EpisodeType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * $option parameter is mandatory but not used
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'code',
                TextType::class,
                [
                    'required'      => false,
                    'property_path' => 'importCode',
                ]
            )
            ->add(
                'rank',
                NumberType::class,
                [
                    'required' => true,
                ]
            )
            ->add(
                'name',
                TextType::class,
                [
                    'required'      => false,
                    'property_path' => 'name',
                ]
            )
            ->add(
                'duration',
                TextType::class,
                [
                    'required' => false,
                ]
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class'         => Episode::class,
                'csrf_protection'    => false,
                'allow_extra_fields' => true,
            ]
        );
    }
}
