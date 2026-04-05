<?php

namespace App\Form;

use App\Entity\Activite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ActiviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $type = strtolower($options['type_jeu']);

        $builder
            ->add('nom')
            ->add('niveau');

        // 🟢 PHRASE AVEC CHOIX ✅
        if ($type === 'emotion_phrase') {
            $builder
                ->add('question', TextareaType::class, [
                    'label' => 'Phrase à compléter'
                ])
                ->add('choix1', TextType::class)
                ->add('choix2', TextType::class)
                ->add('choix3', TextType::class)
                ->add('choix4', TextType::class)
                ->add('bonneReponse', TextType::class, [
                    'label' => 'Bonne réponse'
                ]);
        }

        // 🧩 PUZZLE
        elseif ($type === 'puzzle') {
            $builder->add('imageFile', FileType::class, [
                'mapped' => false,
                'required' => true
            ]);
        }

        // 😊 EMOTION IMAGE
        elseif ($type === 'emotion') {
            $builder
                ->add('imageFile', FileType::class, [
                    'mapped' => false,
                    'required' => true
                ])
                ->add('bonneReponse', TextType::class);
        }

        // 🔍 INTRUS
        elseif ($type === 'intrus') {
            $builder
                ->add('image1', FileType::class, ['mapped'=>false, 'required'=>true])
                ->add('image2', FileType::class, ['mapped'=>false, 'required'=>true])
                ->add('image3', FileType::class, ['mapped'=>false, 'required'=>true])
                ->add('image4', FileType::class, ['mapped'=>false, 'required'=>true])
                ->add('bonneReponse', TextType::class);
        }

        // ❓ QUIZ
        elseif ($type === 'quiz') {
            $builder
                ->add('question', TextareaType::class)
                ->add('choix1')
                ->add('choix2')
                ->add('choix3')
                ->add('choix4')
                ->add('bonneReponse');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Activite::class,
            'type_jeu' => null,
        ]);
    }
}