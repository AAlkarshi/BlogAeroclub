<?php
// src/Form/TaskType.php

namespace App\Form;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')  // Champ 'title' du formulaire
            ->add('description')  // Champ 'description' du formulaire
            ->add('dueDate', null, [
                'widget' => 'single_text',  // Champ 'dueDate' avec widget de type 'single_text'
                'attr' => ['class' => 'datepicker']  // Attribut HTML supplémentaire pour le champ 'dueDate'
            ])
            // Ajoutez d'autres champs selon votre besoin
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => Task::class,
            'csrf_protection' => true,    // Activer CSRF pour ce formulaire
            'csrf_field_name' => '_token',  // Nom du champ HTML caché qui stocke le jeton CSRF
            'csrf_token_id'   => 'task_item',  // Identifiant utilisé pour générer le jeton CSRF
        ]);
    }
}
