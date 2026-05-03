<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/assistant-ia')]
class AssistantIAController extends AbstractController
{
    // Mots-clés arabes mappés vers leurs équivalents français
    private const AR_MAP = [
        'قلق' => 'anxiété', 'توتر' => 'stress', 'خوف' => 'peur',
        'نوم' => 'sommeil', 'تعب' => 'fatigue',
        'غضب' => 'colère', 'أزمة' => 'crise', 'سلوك' => 'comportement',
        'تواصل' => 'communication', 'كلام' => 'parler',
        'أكل' => 'manger', 'طعام' => 'alimentation',
        'أصدقاء' => 'ami', 'مدرسة' => 'école',
        'روتين' => 'routine', 'تغيير' => 'changement',
        'تقدم' => 'progrès', 'جلسة' => 'session',
        'إرهاق' => 'épuisé', 'وحيد' => 'seul',
        'دواء' => 'médicament', 'مرحبا' => 'bonjour', 'شكرا' => 'merci',
        'مساعدة' => 'aide',
    ];

    private const RULES = [
        // Détresse / urgence
        ['keywords' => ['crise', 'urgence', 'danger', 'blesser', 'mal', 'panique'],
         'response' => "⚠️ Je sens que vous traversez un moment difficile. Appelez immédiatement votre thérapeute ou le 15 (SAMU). Vous n'êtes pas seul(e)."],

        // Anxiété / stress
        ['keywords' => ['anxieux', 'anxiété', 'stressé', 'stress', 'angoisse', 'inquiet', 'peur'],
         'response' => "💙 Je comprends que vous vous sentez stressé(e). Respirons ensemble :\n\n🌬️ **Exercice de respiration :**\n1. Inspirez par le nez pendant 4 secondes\n2. Retenez votre souffle 4 secondes\n3. Expirez lentement 4 secondes\n4. Répétez 3 fois\n\n🎯 **Technique d'ancrage :** Nommez 5 choses que vous voyez, 4 que vous touchez, 3 que vous entendez.\n\n💬 **Question :** Qu'est-ce qui vous stresse le plus en ce moment ? Parlez-en avec votre thérapeute lors de la prochaine séance."],

        // Sommeil
        ['keywords' => ['dormir', 'sommeil', 'insomnie', 'nuit', 'fatigué', 'fatigue'],
         'response' => "🌙 Le sommeil est essentiel pour le bien-être ! Voici mes conseils :\n\n✅ **Routine du soir :**\n• Éteignez les écrans 30 min avant 📵\n• Heure de coucher régulière (même le week-end)\n• Activité calme : lecture, musique douce 📚\n• Température fraîche dans la chambre\n\n💡 **Astuce TSA :** Les enfants autistes adorent les routines visuelles ! Créez un tableau avec des images pour chaque étape du coucher.\n\n💬 **Question :** Avez-vous essayé une routine visuelle ? Si non, je peux vous guider !"],

        // Comportement / agitation
        ['keywords' => ['agité', 'agitation', 'crise', 'colère', 'comportement', 'meltdown'],
         'response' => "🔥 Les crises sont difficiles, mais vous gérez déjà beaucoup ! Bravo pour votre patience. 💚\n\n🚪 **Pendant la crise :**\n1. Réduisez les stimuli (lumière, bruit) 🔇\n2. Espace calme et sécurisé\n3. Parlez doucement, sans forcer le contact\n4. Restez près mais donnez de l'espace\n\n📝 **Après la crise :**\n• Notez les déclencheurs (heure, lieu, activité)\n• Partagez avec le thérapeute\n• Célébrez le retour au calme 🎉\n\n💬 **Question :** Avez-vous remarqué des déclencheurs récurrents ? Cela aide énormément !"],

        // Communication / langage
        ['keywords' => ['parler', 'communication', 'langage', 'mot', 'verbal', 'pecs', 'pictogramme'],
         'response' => "🗣️ La communication est un voyage, pas une destination ! Chaque effort compte. 🌟\n\n🖼️ **Outils visuels :**\n• Pictogrammes (PECS)\n• Tableaux de communication\n• Gestes / langage des signes\n\n💬 **Techniques :**\n• Phrases courtes et claires\n• Temps de réponse : 10-15 secondes\n• Valorisez CHAQUE tentative 🎉\n• Modélisez le langage (répétez correctement sans corriger)\n\n💡 **Motivation :** Votre enfant communique déjà, même sans mots ! Les regards, gestes, comportements sont des messages.\n\n💬 **Question :** Quels sont les intérêts spéciaux de votre enfant ? Utilisez-les pour motiver la communication !"],

        // Alimentation
        ['keywords' => ['manger', 'alimentation', 'nourriture', 'repas', 'refus', 'sélectif'],
         'response' => "🍽️ L'alimentation sélective est très courante dans le TSA. Vous n'êtes pas seul(e) ! 💚\n\n🥦 **Stratégies :**\n• Introduction progressive (1 nouvel aliment/semaine)\n• Respectez textures et couleurs préférées\n• Mangez ensemble, SANS pression\n• Présentations ludiques (formes amusantes)\n\n🎯 **Objectif :** Pas de bataille ! Le repas = moment agréable.\n\n💡 **Astuce :** Impliquez l'enfant dans la préparation (laver, mélanger). Cela réduit l'anxiété.\n\n💬 **Question :** Votre enfant a-t-il des aliments « sûrs » ? Construisez à partir de là !"],

        // Socialisation
        ['keywords' => ['ami', 'social', 'école', 'camarade', 'isolé', 'relation', 'jouer'],
         'response' => "👫 La socialisation est un défi, mais votre enfant a tellement à offrir ! 🌟\n\n🎮 **Étapes progressives :**\n1. Jeu parallèle (côte à côte, pas ensemble)\n2. Jeu avec règles simples\n3. Interactions courtes et structurées\n4. Groupes de pairs avec encadrement\n\n💡 **Astuce :** Les intérêts spéciaux sont des SUPER portes d'entrée sociale ! Clubs, ateliers thématiques...\n\n🎉 **Célébrez :** Chaque interaction, même brève, est une victoire !\n\n💬 **Question :** Quels sont les passions de votre enfant ? Dinosaures ? Trains ? Utilisons-les !"],

        // Routine / changement
        ['keywords' => ['routine', 'changement', 'transition', 'imprévu', 'planning', 'emploi du temps'],
         'response' => "📅 Les routines sont des ancrages sécurisants pour les enfants TSA. Excellent sujet ! 👏\n\n✅ **Outils visuels :**\n• Emploi du temps illustré 🖼️\n• Timer visuel pour les transitions\n• Histoires sociales pour nouveaux lieux\n• Photos de la journée\n\n🔄 **Changements :**\n• Annoncez à l'avance (avec support visuel)\n• Expliquez le « pourquoi » simplement\n• Gardez des éléments familiers\n\n💡 **Motivation :** La prévisibilité = sécurité. Vous offrez un cadre rassurant ! 💚\n\n💬 **Question :** Utilisez-vous déjà des supports visuels ? Si non, commencez par un simple tableau matin/soir !"],

        // Progrès / session
        ['keywords' => ['progrès', 'amélioration', 'session', 'séance', 'résultat', 'évolution'],
         'response' => "📈 Les progrès dans le TSA sont souvent non-linéaires — c'est NORMAL ! 🌟\n\n🎉 **Célébrez TOUT :**\n• Un regard soutenu\n• Un nouveau mot\n• Une transition réussie\n• Un moment de calme\n\n📊 **Suivez l'évolution :**\nConsultez la section **Suivis** pour visualiser les progrès. Chaque petit pas est une VICTOIRE ! 🏆\n\n💡 **Motivation :** Vous faites un travail INCROYABLE ! Les progrès arrivent, même quand on ne les voit pas immédiatement.\n\n💬 **Question :** Quelle petite victoire avez-vous célébrée cette semaine ? Partagez-la avec moi !"],

        // Épuisement parental
        ['keywords' => ['épuisé', 'épuisement', 'burnout', 'plus', 'craquer', 'seul', 'difficile'],
         'response' => "💙 Votre bien-être compte AUTANT que celui de votre enfant. Vous êtes important(e) ! 🤗\n\n🛑 **Prenez soin de vous :**\n• Répit régulier (même 30 min)\n• Groupes de parents (vous n'êtes pas seul)\n• Activité rien que pour vous\n• Consultation pour VOUS si besoin\n\n💪 **Vous êtes un héros du quotidien !** Ce que vous faites est IMMENSE.\n\n💡 **Rappel :** Un parent reposé = un meilleur parent. Ce n'est PAS de l'égoïsme !\n\n💬 **Question :** Quand avez-vous pris du temps pour vous la dernière fois ? Planifions ensemble un moment de répit !"],

        // Médicaments
        ['keywords' => ['médicament', 'traitement', 'dose', 'ordonnance', 'médecin'],
         'response' => "💊 Pour toute question sur les médicaments ou traitements, consultez impérativement le médecin prescripteur.\n\nJe ne suis pas habilité à donner des conseils médicaux. Votre sécurité est prioritaire ! 💚"],

        // Bonjour / salutation
        ['keywords' => ['bonjour', 'bonsoir', 'salut', 'hello', 'bonne journée'],
         'response' => "👋 Bonjour ! Je suis ravi(e) de vous aider aujourd'hui !\n\nJe suis votre **Assistant IA Thérapeutique TSA** 🤖💙\n\n🎯 **Je peux vous aider avec :**\n• Gestion du comportement 🔥\n• Communication 🗣️\n• Routines et transitions 📅\n• Sommeil 🌙\n• Alimentation 🍽️\n• Socialisation 👫\n• Soutien parental 💙\n\n💬 **Question :** Qu'est-ce qui vous préoccupe aujourd'hui ? Je suis là pour vous écouter et vous guider !"],

        // Merci
        ['keywords' => ['merci', 'super', 'parfait', 'génial', 'bien'],
         'response' => "🎉 Avec grand plaisir ! Vous faites un travail formidable ! 💚\n\nN'hésitez JAMAIS à revenir me parler. Je suis là 24/7 pour vous soutenir entre vos sessions.\n\n💪 **Motivation :** Chaque jour, vous faites une différence dans la vie de votre enfant. Vous êtes incroyable !"],

        // Aide / que faire
        ['keywords' => ['aide', 'aider', 'conseil', 'que faire', 'comment', 'quoi'],
         'response' => "🌟 Je suis là pour vous ! Voici mes domaines d'expertise :\n\n🧠 **Comportement** — crises, agitation, colère\n💬 **Communication** — langage, pictogrammes, PECS\n😴 **Sommeil** — routines nocturnes, insomnie\n🍽️ **Alimentation** — sélectivité, refus alimentaire\n👫 **Socialisation** — amitiés, école, interactions\n📅 **Routines** — transitions, changements, imprévus\n💙 **Soutien parental** — épuisement, motivation\n\n💬 **Question :** Quel sujet vous intéresse le plus ? Parlons-en ensemble !"],
    ];

    #[Route('/message', name: 'app_assistant_ia_message', methods: ['POST'])]
    public function message(Request $request): JsonResponse
    {
        $messageRaw = $request->request->get('message', '');
        $message = strtolower(trim(is_string($messageRaw) ? $messageRaw : ''));

        if (empty($message)) {
            return new JsonResponse(['reply' => 'Veuillez saisir un message.']);
        }

        // Traduire les mots-clés arabes vers le français pour la correspondance
        foreach (self::AR_MAP as $ar => $fr) {
            if (str_contains($message, $ar)) {
                $message .= ' ' . $fr;
            }
        }

        // Recherche de correspondance avec les règles
        foreach (self::RULES as $rule) {
            foreach ($rule['keywords'] as $keyword) {
                if (str_contains($message, $keyword)) {
                    return new JsonResponse(['reply' => $rule['response']]);
                }
            }
        }

        // Réponse par défaut si aucune correspondance
        return new JsonResponse([
            'reply' => "🤔 Je n'ai pas bien compris votre question, mais je suis là pour vous aider !\n\n💬 **Essayez de me parler de :**\n• Comportement (crises, agitation)\n• Communication (langage, pictogrammes)\n• Sommeil (routines, insomnie)\n• Alimentation (sélectivité)\n• Socialisation (amis, école)\n• Routines (changements, transitions)\n• Soutien parental (épuisement)\n\n💚 **Question :** Qu'est-ce qui vous préoccupe le plus en ce moment ? Je suis là pour écouter et guider !\n\n⚠️ Pour une aide urgente, contactez directement votre thérapeute."
        ]);
    }
}
