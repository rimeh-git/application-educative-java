<?php

namespace App\Form;

use App\Entity\Session;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateHeure', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'label' => '📅 Date et Heure de la séance',
                'label_attr' => ['class' => 'form-label text-indigo-700 font-semibold'],
                'attr' => [
                    'class' => 'form-input-calendar',
                ],
            ])
            ->add('duree', IntegerType::class, [
                'label' => '⏱️ Durée (minutes)',
                'label_attr' => ['class' => 'form-label text-indigo-700 font-semibold'],
                'attr' => [
                    'class' => 'form-input-number',
                    'placeholder' => 'Ex: 60 minutes',
                    'min' => 1,
                    'max' => 300,
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => '🎯 Type de thérapie',
                'label_attr' => ['class' => 'form-label text-indigo-700 font-semibold'],
                'choices' => [
                    'ABA (Applied Behavior Analysis)' => 'ABA',
                    'PECS (Picture Exchange Communication)' => 'PECS',
                    'Orthophonie' => 'Orthophonie',
                    'Psychomotricité' => 'Psychomotricité',
                    'Ergothérapie' => 'Ergothérapie',
                ],
                'attr' => [
                    'class' => 'form-input-select',
                ],
                'placeholder' => '🔽 Sélectionnez un type de thérapie',
            ])
            ->add('nomResponsable', TextType::class, [
                'label' => '👤 Nom du responsable',
                'required' => false,
                'label_attr' => ['class' => 'form-label text-indigo-700 font-semibold'],
                'attr' => [
                    'class' => 'form-input-text',
                    'placeholder' => 'Nom du parent ou responsable',
                ],
            ])
            ->add('telephoneResponsable', TelType::class, [
                'label' => '📞 Téléphone du responsable',
                'required' => false,
                'label_attr' => ['class' => 'form-label text-indigo-700 font-semibold'],
                'attr' => [
                    'class' => 'form-input-text',
                    'placeholder' => 'Ex: 29 123 456',
                ],
            ])
            ->add('parentEmail', EmailType::class, [
                'label' => '✉️ Email du responsable',
                'required' => false,
                'label_attr' => ['class' => 'form-label text-indigo-700 font-semibold'],
                'attr' => [
                    'class' => 'form-input-text',
                    'placeholder' => 'parent@example.com',
                ],
            ])
            ->add('statut', ChoiceType::class, [
                'label' => '📊 Statut de la séance',
                'label_attr' => ['class' => 'form-label text-indigo-700 font-semibold'],
                'choices' => [
                    '📅 Planifiée' => 'PLANIFIEE',
                    '▶️ En cours' => 'EN_COURS',
                    '✅ Terminée' => 'TERMINEE',
                    '❌ Annulée' => 'ANNULEE',
                ],
                'attr' => [
                    'class' => 'form-input-select',
                ],
            ])
            ->add('niveauAgitation', ChoiceType::class, [
                'label' => '😊 Niveau d\'agitation de l\'enfant',
                'label_attr' => ['class' => 'form-label text-indigo-700 font-semibold'],
                'choices' => [
                    '😊 Calme - Bonne humeur' => 'CALME',
                    '😰 Agité - Nerveux' => 'AGITE',
                    '😭 Crise - Difficile à gérer' => 'CRISE',
                ],
                'attr' => [
                    'class' => 'form-input-select',
                ],
                'placeholder' => '🔽 Évaluez le niveau d\'agitation',
            ])
            ->add('techniqueUtilisee', TextType::class, [
                'label' => '🛠️ Technique utilisée',
                'required' => false,
                'label_attr' => ['class' => 'form-label text-indigo-700 font-semibold'],
                'attr' => [
                    'class' => 'form-input-text',
                    'placeholder' => 'Ex: Renforcement positif, Modélisation, Désensibilisation...',
                ],
            ])
            ->add('objectifSeance', TextareaType::class, [
                'label' => '🎯 Objectif principal de la séance',
                'required' => false,
                'label_attr' => ['class' => 'form-label text-indigo-700 font-semibold'],
                'attr' => [
                    'class' => 'form-input-textarea',
                    'rows' => 4,
                    'placeholder' => 'Décrivez l\'objectif thérapeutique visé pour cette séance...',
                ],
            ])
            ->add('notes', TextareaType::class, [
                'label' => '📝 Observations et notes cliniques',
                'required' => false,
                'label_attr' => ['class' => 'form-label text-indigo-700 font-semibold'],
                'attr' => [
                    'class' => 'form-input-textarea',
                    'rows' => 5,
                    'placeholder' => 'Notez les comportements observés, les réactions de l\'enfant, le contexte particulier...',
                ],
            ])
            ->add('meetLink', TextType::class, [
                'label' => '🎥 Lien de visioconférence',
                'required' => false,
                'label_attr' => ['class' => 'form-label text-indigo-700 font-semibold'],
                'attr' => [
                    'class' => 'form-input-text',
                    'placeholder' => 'Généré automatiquement (Jitsi Meet)',
                    'readonly' => true,
                ],
                'help' => '<div class="bg-green-50 border border-green-300 rounded-lg p-3 mt-2"><p class="text-green-900 text-sm"><i class="fas fa-check-circle"></i> Un lien Jitsi Meet sera généré automatiquement - Accès direct sans autorisation</p></div>',
                'help_html' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
            'meet_helper_url' => '/session/meet-helper',
        ]);
    }
}