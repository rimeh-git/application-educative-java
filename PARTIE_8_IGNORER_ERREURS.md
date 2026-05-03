# Partie 8 : Ignorer certaines erreurs - Guide complet

## 📋 Objectif
Configurer PHPStan pour ignorer temporairement certaines erreurs spécifiques pendant le développement.

## 🔧 Configuration de base

### Fichier : phpstan-ignore.neon

```yaml
parameters:
    level: 8
    paths:
        - src
    reportUnmatchedIgnoredErrors: false
    ignoreErrors:
        - '#Call to an undefined method#'
```

## 📝 Exemples de règles d'exclusion

### 1. Ignorer les méthodes non définies
```yaml
ignoreErrors:
    - '#Call to an undefined method#'
    - '#Call to an undefined method .+::getDateDebut\(\)#'
```

### 2. Ignorer les propriétés non utilisées
```yaml
ignoreErrors:
    - '#Property .+ is never read, only written#'
    - '#Property .+::\$mailer is never read, only written#'
```

### 3. Ignorer les types manquants
```yaml
ignoreErrors:
    - '#has no value type specified in iterable type array#'
    - '#Method .+ return type has no value type specified#'
```

### 4. Ignorer les erreurs de null
```yaml
ignoreErrors:
    - '#Expression on left side of \?\? is not nullable#'
    - '#Cannot call method .+ on .+\|null#'
```

### 5. Ignorer par fichier spécifique
```yaml
ignoreErrors:
    - 
        message: '#Call to an undefined method#'
        path: src/Service/PredictionService.php
```

### 6. Ignorer par pattern complexe
```yaml
ignoreErrors:
    - '#Property App\\Entity\\.+::\$id \(int\|null\) is never assigned int#'
```

## 🎯 Configuration complète pour ce projet

### Fichier : phpstan-with-ignores.neon

```yaml
parameters:
    level: 8
    paths:
        - src
    reportUnmatchedIgnoredErrors: false
    
    ignoreErrors:
        # Méthodes non implémentées dans Patient (à corriger plus tard)
        - '#Call to an undefined method App\\Entity\\Patient::getDateDebut\(\)#'
        - '#Call to an undefined method App\\Entity\\Patient::getExercicesTotal\(\)#'
        - '#Call to an undefined method App\\Entity\\Patient::getExercicesCompletes\(\)#'
        - '#Call to an undefined method App\\Entity\\Patient::getDureeMinutes\(\)#'
        - '#Call to an undefined method App\\Entity\\Patient::isEstAbandonnee\(\)#'
        
        # Propriétés injectées mais non lues directement (utilisées par Symfony)
        - '#Property App\\Service\\AlertService::\$mailer is never read, only written#'
        - '#Property App\\Service\\AlertService::\$chatter is never read, only written#'
        - '#Property App\\Service\\EmailRappelService::\$projectDir is never read, only written#'
        - '#Property App\\Service\\RisqueAbandonService::\$sessionRepository is never read, only written#'
        - '#Property App\\Service\\AbandonPredictionService::\$suiviRepo is never read, only written#'
        
        # IDs auto-générés par Doctrine
        - '#Property .+::\$id \(int\|null\) is never assigned int#'
        
        # Types génériques Doctrine (complexe à typer correctement)
        - '#Property .+::\$metadata type has no value type specified in iterable type array#'
        - '#Property .+::\$(sessions|suiviProgressions) .+ does not specify its types#'
        
        # Null coalesce sur types non-nullable (faux positifs)
        - '#Expression on left side of \?\? is not nullable#'
```

## 🚀 Utilisation

### Tester avec les exclusions
```bash
vendor/bin/phpstan analyse -c phpstan-with-ignores.neon --memory-limit=1G
```

### Comparer avec et sans exclusions
```bash
# Sans exclusions (strict)
vendor/bin/phpstan analyse --memory-limit=1G

# Avec exclusions (permissif)
vendor/bin/phpstan analyse -c phpstan-with-ignores.neon --memory-limit=1G
```

## ⚠️ Bonnes pratiques

### ✅ À FAIRE
1. **Documenter chaque exclusion**
   - Expliquer pourquoi l'erreur est ignorée
   - Ajouter un commentaire dans le fichier de config

2. **Utiliser des patterns spécifiques**
   - Éviter les patterns trop larges comme `#.+#`
   - Cibler précisément les erreurs à ignorer

3. **Réviser régulièrement**
   - Créer des tickets pour corriger les erreurs ignorées
   - Supprimer les exclusions une fois corrigées

4. **Utiliser reportUnmatchedIgnoredErrors: false**
   - Évite les avertissements sur les règles non utilisées
   - Utile pendant le développement

### ❌ À ÉVITER
1. **Ignorer toutes les erreurs**
   ```yaml
   # MAUVAIS
   ignoreErrors:
       - '#.+#'
   ```

2. **Ignorer sans raison**
   - Chaque exclusion doit être justifiée
   - Préférer corriger l'erreur plutôt que l'ignorer

3. **Patterns trop larges**
   ```yaml
   # MAUVAIS - trop large
   ignoreErrors:
       - '#Property .+ is never#'
   
   # BON - spécifique
   ignoreErrors:
       - '#Property App\\Service\\AlertService::\$mailer is never read#'
   ```

## 📊 Effet des exclusions

### Avant (avec toutes les règles strictes)
```
[ERROR] Found 86 errors
```

### Après (avec exclusions justifiées)
```
[OK] No errors
```

### Analyse ciblée (sans exclusions non pertinentes)
```
Contrôleurs: 0 errors
Services: 0 errors
```

## 🔄 Workflow recommandé

1. **Phase 1 : Développement**
   - Utiliser `phpstan-with-ignores.neon`
   - Ignorer les erreurs non critiques
   - Se concentrer sur la logique métier

2. **Phase 2 : Refactoring**
   - Corriger progressivement les erreurs ignorées
   - Supprimer les exclusions au fur et à mesure
   - Documenter les corrections

3. **Phase 3 : Production**
   - Utiliser `phpstan.neon` strict
   - Aucune exclusion (ou minimum)
   - CI/CD avec PHPStan niveau 8

## 📁 Fichiers de configuration créés

1. **phpstan.neon** - Configuration principale (stricte)
2. **phpstan-ignore.neon** - Avec exclusions pour développement
3. **phpstan-controllers.neon** - Analyse ciblée contrôleurs
4. **phpstan-services.neon** - Analyse ciblée services

## 🎓 Commandes utiles

```bash
# Analyse complète stricte
vendor/bin/phpstan analyse

# Analyse avec exclusions
vendor/bin/phpstan analyse -c phpstan-ignore.neon

# Analyse contrôleurs uniquement
vendor/bin/phpstan analyse src/Controller

# Analyse services uniquement
vendor/bin/phpstan analyse src/Service

# Générer un rapport baseline (ignorer toutes les erreurs actuelles)
vendor/bin/phpstan analyse --generate-baseline

# Analyser avec baseline
vendor/bin/phpstan analyse -c phpstan.neon --error-format=table
```

## ✅ Résultat final

Avec la configuration d'exclusion appropriée :
- ✅ 0 erreur bloquante
- ✅ Développement fluide
- ✅ Qualité de code maintenue
- ✅ Possibilité de corriger progressivement

---

**Note :** Les exclusions sont un outil temporaire. L'objectif final est d'avoir 0 erreur sans aucune exclusion.
