# Guide de Développement (DDD, TDD, Hexagonal Architecture)

Ce document définit les principes et les règles de développement à suivre pour le projet.

## 1. Principes Fondamentaux
- **DDD (Domain-Driven Design) :** Le cœur métier (Domaine) doit être isolé de l'infrastructure et de l'interface utilisateur.
- **TDD (Test-Driven Development) :** Écrire un test qui échoue, le faire passer avec le code minimal, puis refactoriser.
- **Architecture Hexagonale :** Séparer le domaine (Inside) de l'infrastructure (Outside) via des ports (interfaces) et des adaptateurs.
- **Clean Code :**
    - **DRY (Don't Repeat Yourself) :** Éviter la duplication.
    - **KISS (Keep It Simple, Stupid) :** Favoriser la simplicité.
    - **SOLID :**
        - Single Responsibility (S)
        - Open/Closed (O)
        - Liskov Substitution (L)
        - Interface Segregation (I)
        - Dependency Inversion (D)

## 2. Structure du Code (Architecture Hexagonale)
Le code source dans `src/` doit être organisé comme suit :
- `Domain/` : Contient les entités métier, les objets de valeur (Value Objects), les exceptions métier et les interfaces des dépôts (Ports). Ne doit dépendre d'aucune bibliothèque externe (hormis PHP lui-même).
- `Application/` : Contient les cas d'utilisation (Services applicatifs) qui orchestrent le domaine.
- `Infrastructure/` : Contient les implémentations concrètes des interfaces du domaine (Adaptateurs) : persistence, clients API (Solr), etc.
- `UserInterface/` : Contient les contrôleurs, les commandes console, etc.

## 3. Workflow TDD
1. Créer un test unitaire ou d'intégration dans `tests/`.
2. Lancer le test et vérifier qu'il échoue.
3. Implémenter le code minimal dans `src/` pour faire passer le test.
4. Lancer le test et vérifier qu'il passe.
5. Refactoriser le code tout en gardant le test vert.

### 4. Tests et Qualité du Code
Les tests doivent être organisés parallèlement au code source dans `tests/` :
- `tests/Domain/` : Tests unitaires pour les entités et objets du domaine.
- `tests/Application/` : Tests unitaires pour les cas d'utilisation (Use Cases) avec des doublures (mocks).
- `tests/Infrastructure/` : Tests unitaires et d'intégration pour les adaptateurs (Solr, BD, etc.).
- `tests/Integration/` : Tests d'intégration de bout en bout impliquant plusieurs couches (ex: UseCase + Solr réel).
- `tests/Controller/` : Tests fonctionnels (WebTestCase) pour l'interface utilisateur.

Toute nouvelle fonctionnalité doit être accompagnée de ses tests correspondants.
