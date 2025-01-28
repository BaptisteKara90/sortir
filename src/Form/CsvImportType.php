<?php

namespace App\Form;

use App\Entity\Site;
use App\Repository\SiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CsvImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom',
                'query_builder' => function (SiteRepository $siteRepository) {
                    return $siteRepository->createQueryBuilder('s')
                        ->where('s.actif = :actif')
                        ->setParameter('actif', true);
                },
                'placeholder' => 'Selectionner une site',
                'required' => true,
            ])
            ->add('file', FileType::class, [
                'mapped' => false,
                'constraints' => [new File([
                    "maxSize" => '1024k',
                    'maxSizeMessage' => 'Le fichier est trop volumineux',
                    'mimeTypes' => ['text/csv', 'text/plain'],])],
                'label' => 'Fichier CSV à importer',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'method' => 'POST'
        ]);
    }
}
