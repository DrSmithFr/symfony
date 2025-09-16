<?php

declare(strict_types = 1);

namespace App\Form;

use App\Entity\Series;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SeriesType extends AbstractType
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
                'locale',
                ChoiceType::class,
                [
                    'required' => false,
                    'choices'  => [
                        'Français' => 'fr',
                        'Anglais'  => 'us',
                    ],
                ]
            )
            ->add(
                'import_code',
                TextType::class,
                [
                    'required'      => false,
                    'property_path' => 'importCode',
                ]
            )
            ->add(
                'name',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'description',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'image',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'seasons',
                CollectionType::class,
                [
                    'entry_type'   => SeasonType::class,
                    'allow_add'    => true,
                    'allow_delete' => true,
                    'by_reference' => true,
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
                'data_class'         => Series::class,
                'csrf_protection'    => false,
                'allow_extra_fields' => true,
            ]
        );
    }
}
