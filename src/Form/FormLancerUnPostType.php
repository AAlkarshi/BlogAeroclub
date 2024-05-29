<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\File;



class FormLancerUnPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /*
        $builder
            ->add('content')
            ->add('image')
            ->add('utilisateur')
            ->add('article')
        ;
        */


       $builder ->add('nomcategorie', TextType::class, [
            'label' => 'Nom de la catégorie',
        ])
        ->add('image', FileType::class, [
            'label' => 'Ajout image (facultatif)',
            'mapped' => false,
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '1024k',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/png',
                    ],
                    'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG ou PNG)',
                ])
            ],
        ])
        ->add('commentaire', TextareaType::class, [
            'label' => 'Commentaire',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
