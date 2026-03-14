# Liste de Vérification et Nettoyage (Routine de Fin de Tâche)

Ce document récapitule les étapes à suivre systématiquement avant de considérer une tâche comme terminée (nettoyage, validation et qualité).

## 1. Nettoyage du Code (Linter & Style)

Avant de soumettre, s'assurer que le code respecte les standards du projet :

- [ ] **Importations de fonctions globales** : Utiliser `use function` au lieu de l'anti-slash (ex: `use function sprintf;`).
- [ ] **Type Hinting & Strict Types** : Chaque nouveau fichier PHP doit commencer par `declare(strict_types=1);`.
- [ ] **Attributs Symfony/PHP** : Utiliser `#[Override]` sur les méthodes implémentant une interface ou surchargeant une classe mère.
- [ ] **Mago (Lint/Format/Analyze)** : Exécuter la suite complète de vérifications statiques.
  ```bash
  bash scripts/mago-check.sh
  ```
  *(Note : `mago format` s'occupe de l'indentation et du style standard).*

## 2. Validation Fonctionnelle (Tests)

- [ ] **Tests Unitaires/Intégration** : Lancer la suite de tests PHPUnit.
  ```bash
  bash scripts/run-tests.sh
  ```
- [ ] **Tests de Non-Régression** : S'assurer que les changements n'ont pas cassé d'autres parties du système (couverture globale).

## 3. Vérification Globale (Script combiné)

Pour une vérification rapide de tous les points ci-dessus, utiliser le script global :
```bash
bash scripts/check-all.sh
```

## 4. Documentation & Maintenance

- [ ] **Mise à jour des Guidelines** : Si une nouvelle règle a été décidée pendant la tâche, l'ajouter dans `instruction/development_guidelines.md`.
- [ ] **Commentaires** : Supprimer les commentaires de debug (`dump`, `var_dump`, `console.log`) et le code mort.
- [ ] **Messages de Commit/Submit** : Résumer clairement les changements apportés et les points de vérification passés.
