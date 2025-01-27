<?php

namespace App\Form;

use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;

class UserFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => false,
                'label' => 'Nom',
            ])
            ->add('prenom', TextType::class, [
                'required' => false,
                'label' => 'Prenom',
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'label' => 'Email',
            ])
            ->add('site', EntityType::class, [
                'required' => false,
                'class' => Site::class,
                'choice_label' => 'nom',
                'placeholder' => 'Selectionner une site',
            ])
            ->add('actif', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'Actif' => "actif",
                    'Inactif' => 'inactif',
                ],
                'placeholder' => 'Selectionner un statut',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'method' => 'GET'
        ]);
    }
}
