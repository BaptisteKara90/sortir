<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Repository\SiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie'
            ])
            ->add('debut', DateTimeType::class, [
                'label' => 'Heure de début de la sortie',
                'widget' => 'single_text',
            ])
            ->add('duree', IntegerType::class,[
                'label'=> 'Durée de la sortie',
            ])
            ->add('dateLimitInscription', DateTimeType::class, [
                'label' => 'Date limite d\'inscription',
                'widget' => 'single_text',
            ])
            ->add('nbMaxParticipant')
            ->add('infosSortie', TextareaType::class)
            ->add('etat', EntityType::class, [
                'class' => Etat::class,
                'choice_label' => 'libelle',
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom',
                'query_builder' => function (SiteRepository $siteRepository) {
                return $siteRepository->createQueryBuilder('s')
                    ->where('s.actif = :actif')
                    ->setParameter('actif', true);
                }
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom', // Nom du lieu à afficher
                'placeholder' => 'Sélectionnez un lieu existant',
                'required' => false,
            ])
            ->add('nouveauLieu', LieuType::class, [
                'mapped' => false, // Ce champ ne correspond pas directement à une propriété de l'entité
                'required' => false,
            ])
            ->add('nouvelleVille', VilleType::class, [
                'mapped' => false, // Ce champ ne correspond pas directement à une propriété de l'entité
                'required' => false,
            ])
            ->add('active', HiddenType::class, [
                "data" => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
