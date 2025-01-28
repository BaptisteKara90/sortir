<?php

namespace App\Form;

use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $userSite = $options['userSite'] ?? null;

        $builder
            ->add('site', EntityType::class, [
                'required' => false,
                'class' => Site::class,
                'choice_label' => 'nom',
                'placeholder' => 'Tous les sites',
                'data' => $userSite,
                'attr' => [
                    'class' => 'form-filterSortie',
                ]
            ])
            ->add('content', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-filterSortie',
                ]
            ])
            ->add('dateDebut', DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-filterSortie',
                ]
            ])
            ->add('dateFin', DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-filterSortie',
                ]
            ])
            ->add('organisateur', CheckboxType::class, [
                'required' => false,
                'label' => "Sorties dont je suis l'organisateur/trice",
                'attr' => [
                    'class' => 'form-filterSortie',
                ]
            ])
            ->add('inscrit', choiceType::class, [
                'required' => false,
                'label' => 'Inscrit',
                'choices' => [
                    'Oui' => 'inscrit',
                    'Non' => 'non inscrit',
                    'Tout' => 'tout',
                ],
                'attr' => [
                    'class' => 'form-inscrit',
                ]
            ])
            ->add('sortiePassee', CheckboxType::class, [
                'required' => false,
                'label' => 'Sorties Passées',
                'attr' => [
                    'class' => 'form-sortiePassee',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'userSite' => null,
            'data_class' => null,
        ]);
    }
}
