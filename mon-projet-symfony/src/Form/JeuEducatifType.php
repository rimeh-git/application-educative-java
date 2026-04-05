<?php

namespace App\Form;

use App\Entity\JeuEducatif;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class JeuEducatifType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Quiz' => 'quiz',
                    'Puzzle' => 'puzzle',
                    'Intrus' => 'intrus',
                    'Emotion' => 'emotion',
                    'Emotion Phrase' => 'emotion_phrase',
                ],
                'placeholder' => 'Choisir un type'
            ])
            ->add('niveau', ChoiceType::class, [
                'choices' => [
                    'Facile' => 'facile',
                    'Moyen' => 'moyen',
                    'Difficile' => 'difficile',
                ]
            ])
            ->add('description', TextType::class, [
                'required' => false
            ])
            ->add('imageFile', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Image du jeu'
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => JeuEducatif::class,
        ]);
    }
}