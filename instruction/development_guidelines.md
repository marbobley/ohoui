# Guide de Développement (DDD, TDD, Hexagonal Architecture)

Ce document définit les principes et les règles de développement à suivre pour le projet.

## 1. Principes Fondamentaux
- **DDD (Domain-Driven Design) :** Le cœur métier (Domaine) doit être isolé de l'infrastructure et de l'interface utilisateur.
- **TDD (Test-Driven Development) :** Écrire un test qui échoue, le faire passer avec le code minimal, puis refactoriser.
- **Architecture Hexagonale :** Séparer le domaine (Inside) de l'infrastructure (Outside) via des ports (interfaces) et des adaptateurs.
- **Clean Code :**
    - **DRY (Don't Repeat Yourself) :** Éviter la duplication.
    - **KISS (Keep It Simple, Stupid) :** Favoriser la simplicité.
    - **SOLID :** SRP, OCP, LSP, ISP, DIP.

## 2. Structure du Code (Architecture Hexagonale)
Le code source dans `src/` doit être organisé comme suit :
- `Domain/` : Contient les entités métier, les objets de valeur (Value Objects), les exceptions métier et les interfaces des dépôts (Ports). Ne doit dépendre d'aucune bibliothèque externe.
- `Application/` : Contient les cas d'utilisation (Services applicatifs) qui orchestrent le domaine.
- `Infrastructure/` : Contient les implémentations concrètes des interfaces du domaine (Adaptateurs) : persistence, clients API (Solr), etc.
- `Controller/` : Contient les contrôleurs Web et API.
- `Command/` : Contient les commandes console Symfony.
- `Service/` : Services transversaux techniques.
- `Entity/` / `Repository/` : Persistence Doctrine (si nécessaire).

## 3. Workflow TDD
1. Créer un test unitaire ou d'intégration dans `tests/`.
2. Lancer le test et vérifier qu'il échoue.
3. Implémenter le code minimal dans `src/` pour faire passer le test.
4. Lancer le test et vérifier qu'il passe.
5. Refactoriser le code tout en gardant le test vert.

## 4. Tests et Qualité du Code
Les tests doivent être organisés parallèlement au code source dans `tests/` :
- `tests/Domain/`, `tests/Application/`, `tests/Infrastructure/`, `tests/Integration/`, `tests/Controller/`.

### Qualité et Sécurité (PHP)
- **Appels de Fonctions Globales :** Préférer l'utilisation de `use function <nom_de_la_fonction>;` en haut du fichier plutôt que l'utilisation de l'anti-slash (`\`) directement dans le code (ex: `use function str_contains;`).
- **Linter :** Utiliser `mago` pour valider le code avant de soumettre.

## 5. Gestion des versions (Git)
- **Interdiction Formelle pour l'IA :** Ne JAMAIS effectuer de commandes `git add`, `git commit`, `git pull`, `git fetch`, `git push` ou toute autre opération de modification de l'index ou de l'historique Git. L'IA doit se contenter de modifier les fichiers. La gestion de l'index et des commits est réservée à l'utilisateur humain.

Toute nouvelle fonctionnalité doit être accompagnée de ses tests correspondants.
