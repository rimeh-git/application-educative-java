<?php

namespace App\Form;

use App\Entity\JeuEducatif;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

// Types
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

// Validations
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\File;

class JeuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            // 🎮 TYPE
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Quiz' => 'quiz',
                    'Puzzle' => 'puzzle',
                    'Intrus' => 'intrus',
                    'Emotion' => 'emotion',
                    'Emotion Phrase' => 'emotion_phrase',
                ],
                'placeholder' => 'Choisir un type',
                'constraints' => [
                    new NotBlank([
                        'message' => '⚠️ Le type est obligatoire'
                    ])
                ],
                'attr' => [
                    'class' => 'form-select'
                ]
            ])

            // 🎯 NIVEAU
            ->add('niveau', ChoiceType::class, [
                'choices' => [
                    'Facile' => 'facile',
                    'Moyen' => 'moyen',
                    'Difficile' => 'difficile',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => '⚠️ Le niveau est obligatoire'
                    ])
                ],
                'attr' => [
                    'class' => 'form-select'
                ]
            ])

            // 📝 DESCRIPTION
            ->add('description', TextareaType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => '⚠️ La description est obligatoire'
                    ]),
                    new Length([
                        'min' => 5,
                        'max' => 255,
                        'minMessage' => 'Minimum 5 caractères',
                        'maxMessage' => 'Maximum 255 caractères'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Décrire le jeu...',
                    'rows' => 3,
                    'maxlength' => 255
                ]
            ])

            // 🖼️ IMAGE
            ->add('imageFile', FileType::class, [
                'mapped' => false,
                'required' => true, // obligatoire pour ajout
                'label' => 'Image du jeu',
                'constraints' => [
                    new NotBlank([
                        'message' => '⚠️ L’image est obligatoire'
                    ]),
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp'
                        ],
                        'mimeTypesMessage' => '⚠️ Formats autorisés : JPG, PNG, WEBP'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JeuEducatif::class,
        ]);
    }
}