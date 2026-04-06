<?php

namespace App\Form;

use App\Entity\Session;
use App\Entity\SuiviProgression;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SuiviProgressionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('session', EntityType::class, [
                'class' => Session::class,
                'choice_label' => function(Session $session) {
                    return sprintf(
                        '#%d • %s • %d min • %s',
                        $session->getId(),
                        $session->getDateHeure()?->format('d/m/Y H:i') ?? 'Date inconnue',
                        $session->getDuree() ?? 0,
                        $session->getType() ?? 'Type non défini'
                    );
                },
                'label' => 'Session de thérapie *',
                'placeholder' => '🔍 Choisir une session...',
                'attr' => [
                    'class' => 'form-select w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition-all'
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-semibold text-gray-700 mb-2'
                ]
            ])
            
            ->add('domaine', ChoiceType::class, [
                'label' => 'Domaine évalué *',
                'choices' => [
                    '🗣️ Communication' => 'COMMUNICATION',
                    '👥 Socialisation' => 'SOCIALISATION',
                    '🧠 Comportement' => 'COMPORTEMENT',
                    '✅ Autonomie' => 'AUTONOMIE',
                    '🏃 Motricité' => 'MOTRICITE',
                    '💡 Cognition' => 'COGNITION',
                ],
                'placeholder' => 'Sélectionner le domaine...',
                'attr' => [
                    'class' => 'form-select w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition-all'
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-semibold text-gray-700 mb-2'
                ]
            ])
            
            ->add('scoreAvant', IntegerType::class, [
                'label' => 'Score Avant /10 *',
                'attr' => [
                    'min' => 0,
                    'max' => 10,
                    'class' => 'score-avant form-input w-full rounded-xl border-2 border-rose-200 focus:border-rose-500 focus:ring-rose-500 text-center text-3xl font-bold text-rose-600 bg-rose-50 shadow-sm transition-all',
                    'placeholder' => '0'
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-semibold text-rose-700 mb-2'
                ]
            ])
            
            ->add('scoreApres', IntegerType::class, [
                'label' => 'Score Après /10 *',
                'attr' => [
                    'min' => 0,
                    'max' => 10,
                    'class' => 'score-apres form-input w-full rounded-xl border-2 border-emerald-200 focus:border-emerald-500 focus:ring-emerald-500 text-center text-3xl font-bold text-emerald-600 bg-emerald-50 shadow-sm transition-all',
                    'placeholder' => '0'
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-semibold text-emerald-700 mb-2'
                ]
            ])
            
            ->add('dateEvaluation', DateType::class, [
                'label' => 'Date d\'évaluation *',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-input w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition-all'
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-semibold text-gray-700 mb-2'
                ]
            ])
            
            ->add('comportementsObserves', TextareaType::class, [
                'label' => '📝 Comportements observés',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'class' => 'form-textarea w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition-all resize-none',
                    'placeholder' => 'Décrivez ce que l\'enfant a fait pendant la séance...'
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-semibold text-gray-700 mb-2'
                ]
            ])
            
            ->add('declencheursIdentifies', TextareaType::class, [
                'label' => '⚠️ Déclencheurs identifiés',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'class' => 'form-textarea w-full rounded-xl border-amber-200 focus:border-amber-500 focus:ring-amber-500 shadow-sm transition-all resize-none bg-amber-50',
                    'placeholder' => 'Qu\'est-ce qui a provoqué stress ou agitation ?'
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-semibold text-amber-700 mb-2'
                ]
            ])
            
            ->add('objectifsRealises', TextareaType::class, [
                'label' => '🎯 Objectifs réalisés',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'class' => 'form-textarea w-full rounded-xl border-blue-200 focus:border-blue-500 focus:ring-blue-500 shadow-sm transition-all resize-none bg-blue-50',
                    'placeholder' => 'Quels objectifs ont été atteints ? À quel pourcentage ?'
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-semibold text-blue-700 mb-2'
                ]
            ])
            
            ->add('recommandationsParent', TextareaType::class, [
                'label' => '🏠 Recommandations pour les parents',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'class' => 'form-textarea w-full rounded-xl border-purple-200 focus:border-purple-500 focus:ring-purple-500 shadow-sm transition-all resize-none bg-purple-50',
                    'placeholder' => 'Exercices et attitudes conseillés à la maison...'
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-semibold text-purple-700 mb-2'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SuiviProgression::class,
        ]);
    }
}