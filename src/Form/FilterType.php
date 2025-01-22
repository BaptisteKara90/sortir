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
        $builder
            ->add('site', EntityType::class, [
                'required' => false,
                'class' => Site::class,
                'choice_label' => 'nom',
            ])
            ->add('content', TextType::class, [
                'required' => false,
            ])
            ->add('dateDebut', DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('dateFin', DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('organisateur', CheckboxType::class, [
                'required' => false,
                'label' => "Sorties dont je suis l'organisateur/trice",
            ])
            ->add('inscrit', choiceType::class, [
                'required' => false,
                'label' => 'Inscrit',
                'choices' => [
                    'Oui' => 'inscrit',
                    'Non' => 'non inscrit',
                    'Tout' => 'tout',
                ]
            ])
            ->add('sortiePassee', CheckboxType::class, [
                'required' => false,
                'label' => 'Sorties Passées',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
